<?php

namespace App\Http\Controllers;

use App\Models\WaterBill;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaterBillController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $waterBills = WaterBill::visibleTo($request->user())
            ->orderBy('reporting_year', 'desc')
            ->orderBy('reporting_month', 'desc')
            ->paginate();

        return view('water-bills.index', compact('waterBills'));
    }

    public function create(): View
    {
        return view('water-bills.create', ['facilities' => WaterBill::FACILITY_FIELDS]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reporting_month' => 'required|integer|between:1,12',
            'reporting_year' => 'required|integer',
            'responder_name' => 'nullable|string',
            'lantaka_annex_a' => 'nullable|numeric|min:0',
            'lantaka_old_4_st' => 'nullable|numeric|min:0',
            'jr_kitchen' => 'nullable|numeric|min:0',
            'main' => 'nullable|numeric|min:0',
            'fws' => 'nullable|numeric|min:0',
            'ppo_shop' => 'nullable|numeric|min:0',
            'aux_old_dorm' => 'nullable|numeric|min:0',
        ]);

        $validated['user_id'] = $request->user()->id;

        WaterBill::create($validated);

        return redirect()->route('water-bills.index')->with('success', 'Water bill record created successfully.');
    }

    public function show(WaterBill $waterBill): View
    {
        $this->authorize('view', $waterBill);

        return view('water-bills.show', array_merge(compact('waterBill'), ['facilities' => WaterBill::FACILITY_FIELDS]));
    }

    public function edit(WaterBill $waterBill): View
    {
        $this->authorize('update', $waterBill);

        return view('water-bills.edit', array_merge(compact('waterBill'), ['facilities' => WaterBill::FACILITY_FIELDS]));
    }

    public function update(Request $request, WaterBill $waterBill)
    {
        $this->authorize('update', $waterBill);

        $validated = $request->validate([
            'reporting_month' => 'required|integer|between:1,12',
            'reporting_year' => 'required|integer',
            'responder_name' => 'nullable|string',
            'lantaka_annex_a' => 'nullable|numeric|min:0',
            'lantaka_old_4_st' => 'nullable|numeric|min:0',
            'jr_kitchen' => 'nullable|numeric|min:0',
            'main' => 'nullable|numeric|min:0',
            'fws' => 'nullable|numeric|min:0',
            'ppo_shop' => 'nullable|numeric|min:0',
            'aux_old_dorm' => 'nullable|numeric|min:0',
        ]);

        $waterBill->update($validated);

        return redirect()->route('water-bills.show', $waterBill)->with('success', 'Water bill record updated successfully.');
    }

    public function destroy(WaterBill $waterBill)
    {
        $this->authorize('delete', $waterBill);

        $waterBill->delete();

        return redirect()->route('water-bills.index')->with('success', 'Water bill record deleted successfully.');
    }
}
