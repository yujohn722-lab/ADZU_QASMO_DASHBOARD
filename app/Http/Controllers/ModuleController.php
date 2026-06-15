<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

abstract class ModuleController extends Controller
{
    protected string $modelClass;

    protected string $routeName;

    protected string $title;

    protected string $description = '';

    protected string $icon = 'bi-clipboard-data';

    protected ?string $placeholderMessage = null;

    protected array $fields = [];

    protected array $tableColumns = [];

    protected array $searchable = ['respondent_name', 'remarks'];

    public function index(Request $request): View
    {
        $records = $this->queryFor($request)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('modules.index', $this->viewData([
            'records' => $records,
            'filters' => $request->only(['search', 'reporting_month', 'reporting_year', 'week_number', 'respondent_name', 'office_unit_name', 'solar_panel_id']),
        ]));
    }

    public function create(): View
    {
        return view('modules.create', $this->viewData([
            'record' => new $this->modelClass,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['user_id'] = $request->user()->id;
        $data = $this->beforeSave($data, $request, null);

        $record = $this->modelClass::create($data);

        return redirect()
            ->route($this->routeName.'.show', $record)
            ->with('status', $this->title.' record created.');
    }

    public function show(Request $request, int $id): View
    {
        $record = $this->findVisibleRecord($request, $id);

        return view('modules.show', $this->viewData([
            'record' => $record,
        ]));
    }

    public function edit(Request $request, int $id): View
    {
        $record = $this->findVisibleRecord($request, $id);

        return view('modules.edit', $this->viewData([
            'record' => $record,
        ]));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $record = $this->findVisibleRecord($request, $id);
        $data = $this->beforeSave($this->validatedData($request), $request, $record);

        $record->update($data);

        return redirect()
            ->route($this->routeName.'.show', $record)
            ->with('status', $this->title.' record updated.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $record = $this->findVisibleRecord($request, $id);
        $record->delete();

        return redirect()
            ->route($this->routeName.'.index')
            ->with('status', $this->title.' record deleted.');
    }

    protected function queryFor(Request $request)
    {
        $query = $this->modelClass::query()->visibleTo($request->user());

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($inner) use ($search) {
                foreach ($this->searchable as $field) {
                    if ($this->hasField($field)) {
                        $inner->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }

        foreach (['reporting_month', 'reporting_year', 'week_number', 'respondent_name', 'office_unit_name', 'solar_panel_id'] as $filter) {
            if ($request->filled($filter) && $this->hasField($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('campus') && $this->modelClass === \App\Models\ElectricityConsumption::class) {
            $field = match ($request->input('campus')) {
                'Salvador' => 'total_salvador_kwh',
                'Kreutz' => 'total_kreutz_kwh',
                'Lantaka' => 'total_lantaka_kwh',
                default => null,
            };

            if ($field) {
                $query->where($field, '>', 0);
            }
        }

        return $query;
    }

    protected function findVisibleRecord(Request $request, int $id): Model
    {
        return $this->modelClass::query()
            ->visibleTo($request->user())
            ->findOrFail($id);
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate($this->rules());
    }

    protected function rules(): array
    {
        return collect($this->fields)
            ->filter(fn (array $field) => isset($field['name'], $field['rules']))
            ->mapWithKeys(fn (array $field) => [$field['name'] => $field['rules']])
            ->all();
    }

    protected function beforeSave(array $data, Request $request, ?Model $record): array
    {
        return $data;
    }

    protected function viewData(array $extra = []): array
    {
        return array_merge([
            'title' => $this->title,
            'description' => $this->description,
            'icon' => $this->icon,
            'routeName' => $this->routeName,
            'fields' => $this->fields,
            'tableColumns' => $this->tableColumns,
            'placeholderMessage' => $this->placeholderMessage,
            'months' => $this->months(),
        ], $extra);
    }

    protected function hasField(string $name): bool
    {
        return collect($this->fields)->contains(fn (array $field) => ($field['name'] ?? null) === $name);
    }

    protected function sumFields(array $data, array $fields): float
    {
        return collect($fields)->sum(fn (string $field) => (float) ($data[$field] ?? 0));
    }

    protected function months(): Collection
    {
        return collect([
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ]);
    }
}
