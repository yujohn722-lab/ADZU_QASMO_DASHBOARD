<?php

namespace App\Http\Controllers;

use App\Models\SolarPerformance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolarPerformanceController extends ModuleController
{
    protected string $modelClass = SolarPerformance::class;

    protected string $routeName = 'solar-performances';

    protected string $title = 'Solar Savings and Performance';

    protected string $description = 'Solar panel generation, monthly kWh output, and estimated savings.';

    protected string $icon = 'bi-sun';

    protected array $searchable = ['respondent_name', 'building_name', 'remarks'];

    protected array $tableColumns = [
        'respondent_name' => 'Respondent',
        'reporting_month' => 'Month',
        'reporting_year' => 'Year',
        'monthly_solar_energy_kwh' => 'Total Generated kWh',
        'estimated_savings' => 'Total Savings',
    ];

    protected array $buildings = [
        'Fr.Ernesto Carretero (FEC) Building' => 'Fr.Ernesto Carretero (FEC) Building',
        'GS Admin' => 'GS Admin',
        'Jose Maria Rosauro SJ Hall' => 'Jose Maria Rosauro SJ Hall',
        'Xavier Hall' => 'Xavier Hall',
        'College Building' => 'College Building',
        'Jesuit Residence' => 'Jesuit Residence',
    ];

    protected array $fields = [
        ['name' => 'respondent_name', 'label' => 'Name of respondent', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'col' => 'col-md-4'],
        ['name' => 'reporting_month', 'label' => 'Reporting month', 'type' => 'month', 'rules' => ['required', 'integer', 'between:1,12'], 'col' => 'col-md-4'],
        ['name' => 'reporting_year', 'label' => 'Reporting year', 'type' => 'number', 'rules' => ['required', 'integer', 'min:2000', 'max:2100'], 'col' => 'col-md-4'],
        ['name' => 'solar_buildings', 'label' => 'Building solar entries', 'type' => 'solar_building_inputs', 'col' => 'col-12'],
        ['name' => 'remarks', 'label' => 'Remarks or notes', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'col' => 'col-12'],
    ];

    public function index(Request $request): View
    {
        $query = SolarPerformance::query()
            ->visibleTo($request->user());

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($inner) use ($search) {
                $inner->where('respondent_name', 'like', "%{$search}%")
                    ->orWhere('building_name', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

        foreach (['reporting_month', 'reporting_year', 'respondent_name'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        $records = $query
            ->selectRaw('MIN(id) as id, respondent_name, reporting_month, reporting_year, SUM(monthly_solar_energy_kwh) as monthly_solar_energy_kwh, SUM(estimated_savings) as estimated_savings, MAX(remarks) as remarks')
            ->groupBy('respondent_name', 'reporting_month', 'reporting_year')
            ->orderByDesc('reporting_year')
            ->orderByDesc('reporting_month')
            ->paginate(10)
            ->withQueryString();

        return view('modules.index', $this->viewData([
            'records' => $records,
            'filters' => $request->only(['search', 'reporting_month', 'reporting_year', 'respondent_name']),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedSolarData($request);
        $rows = $this->preparedBuildingRows($data['solar_buildings']);

        if (empty($rows)) {
            return back()
                ->withErrors(['solar_buildings' => 'Enter generated kWh or estimated savings for at least one building.'])
                ->withInput();
        }

        $created = collect();

        foreach ($rows as $building => $values) {
            $created->push(SolarPerformance::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'respondent_name' => $data['respondent_name'],
                    'reporting_month' => $data['reporting_month'],
                    'reporting_year' => $data['reporting_year'],
                    'building_name' => $building,
                ],
                [
                    'monthly_solar_energy_kwh' => $values['monthly_solar_energy_kwh'],
                    'estimated_savings' => $values['estimated_savings'],
                    'remarks' => $data['remarks'] ?? null,
                ]
            ));
        }

        $created->each(fn (SolarPerformance $record) => $this->notifyAdminsAboutSubmission($record, $request, 'submitted'));

        return redirect()
            ->route($this->routeName.'.index')
            ->with('status', $this->title.' records created for '.$created->count().' building(s).');
    }

    public function edit(Request $request, int $id): View
    {
        $record = $this->findVisibleRecord($request, $id);

        return view('modules.edit', $this->viewData([
            'record' => $record,
            'solarBuildingRows' => $this->relatedBuildingRows($record),
        ]));
    }

    public function show(Request $request, int $id): View
    {
        $record = $this->findVisibleRecord($request, $id);

        return view('modules.show', $this->viewData([
            'record' => $record,
            'reportReview' => $this->reviewFor($record),
            'solarBuildingRows' => $this->relatedBuildingRows($record),
        ]));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $record = $this->findVisibleRecord($request, $id);
        $data = $this->validatedSolarData($request);
        $rows = $this->preparedBuildingRows($data['solar_buildings']);
        $allRows = $this->preparedBuildingRows($data['solar_buildings'], false);

        if (empty($rows)) {
            return back()
                ->withErrors(['solar_buildings' => 'Enter generated kWh or estimated savings for at least one building.'])
                ->withInput();
        }

        $existingRows = $this->relatedBuildingRows($record);
        $updated = collect();

        foreach ($allRows as $building => $values) {
            $hasValues = $values['monthly_solar_energy_kwh'] !== null || $values['estimated_savings'] !== null;
            $solarRecord = $existingRows->get($building);

            if (! $hasValues && ! $solarRecord) {
                continue;
            }

            if (! $hasValues) {
                $solarRecord->delete();
                continue;
            }

            $solarRecord ??= new SolarPerformance(['user_id' => $record->user_id]);
            $solarRecord->fill([
                'respondent_name' => $data['respondent_name'],
                'reporting_month' => $data['reporting_month'],
                'reporting_year' => $data['reporting_year'],
                'building_name' => $building,
                'monthly_solar_energy_kwh' => $values['monthly_solar_energy_kwh'],
                'estimated_savings' => $values['estimated_savings'],
                'remarks' => $data['remarks'] ?? null,
            ]);
            $solarRecord->save();
            $updated->push($solarRecord);
        }

        $updated->each(fn (SolarPerformance $solarRecord) => $this->notifyAdminsAboutSubmission($solarRecord, $request, 'updated'));

        return redirect()
            ->route($this->routeName.'.index')
            ->with('status', $this->title.' records updated for '.$updated->count().' building(s).');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $record = $this->findVisibleRecord($request, $id);
        $deleted = $this->relatedBuildingRows($record)->each->delete()->count();

        return redirect()
            ->route($this->routeName.'.index')
            ->with('status', $this->title.' records deleted for '.$deleted.' building(s).');
    }

    protected function viewData(array $extra = []): array
    {
        return parent::viewData(array_merge($extra, [
            'buildings' => $this->buildings,
            'solarBuildingRows' => $extra['solarBuildingRows'] ?? collect(),
        ]));
    }

    private function validatedSolarData(Request $request): array
    {
        return $request->validate([
            'respondent_name' => ['required', 'string', 'max:255'],
            'reporting_month' => ['required', 'integer', 'between:1,12'],
            'reporting_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'solar_buildings' => ['required', 'array'],
            'solar_buildings.*.monthly_solar_energy_kwh' => ['nullable', 'numeric', 'min:0'],
            'solar_buildings.*.estimated_savings' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    private function preparedBuildingRows(array $submittedRows, bool $filledOnly = true): array
    {
        $rows = collect($this->buildings)
            ->mapWithKeys(function (string $label, string $building) use ($submittedRows) {
                $values = $submittedRows[$building] ?? [];

                return [$building => [
                    'monthly_solar_energy_kwh' => $values['monthly_solar_energy_kwh'] ?? null,
                    'estimated_savings' => $values['estimated_savings'] ?? null,
                ]];
            });

        if ($filledOnly) {
            $rows = $rows->filter(fn (array $values) => $values['monthly_solar_energy_kwh'] !== null || $values['estimated_savings'] !== null);
        }

        return $rows->all();
    }

    private function relatedBuildingRows(Model $record)
    {
        return SolarPerformance::query()
            ->visibleTo(auth()->user())
            ->where('respondent_name', $record->respondent_name)
            ->where('reporting_month', $record->reporting_month)
            ->where('reporting_year', $record->reporting_year)
            ->get()
            ->keyBy('building_name');
    }
}
