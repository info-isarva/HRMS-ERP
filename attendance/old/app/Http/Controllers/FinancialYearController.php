<?php

namespace App\Http\Controllers;

use App\Models\FinancialYear;
use App\Models\SystemSetting;
use App\Services\FinancialYearService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinancialYearController extends Controller
{
    protected $fyService;

    public function __construct(FinancialYearService $fyService)
    {
        $this->middleware('auth');
        $this->middleware('role:admin,super_admin');
        $this->fyService = $fyService;
    }

    public function index()
    {
        $financialYears = FinancialYear::orderBy('start_date', 'desc')->get();
        $fyStartMonth = (int) SystemSetting::get('fy_start_month', 4);
        
        return view('financial-years.index', compact('financialYears', 'fyStartMonth'));
    }

    public function store(Request $request)
    {
        return redirect()->route('financial-years.index')->with('error', 'Manual creation is disabled. Financial Years are synced from Payroll.');
    }

    public function update(Request $request, FinancialYear $financialYear)
    {
        return redirect()->route('financial-years.index')->with('error', 'Manual update is disabled. Financial Years are synced from Payroll.');
    }

    public function activate($id)
    {
        FinancialYear::where('is_active', true)->update(['is_active' => false]);
        FinancialYear::where('id', $id)->update(['is_active' => true]);
        
        $this->fyService->clearCache();

        return redirect()->route('financial-years.index')->with('success', 'Active Financial Year updated successfully.');
    }

    public function destroy(FinancialYear $financialYear)
    {
        return redirect()->route('financial-years.index')->with('error', 'Manual deletion is disabled. Financial Years are synced from Payroll.');
    }

    public function updateStartMonth(Request $request)
    {
        return redirect()->route('financial-years.index')->with('error', 'Settings are managed via Payroll synchronization.');
    }

    public function switch(Request $request)
    {
        if ($request->fy_id === 'default') {
            session()->forget('selected_financial_year_id');
            return back()->with('success', 'Switched to default active financial year.');
        }

        $request->validate([
            'fy_id' => 'required|exists:financial_years,id',
        ]);

        session(['selected_financial_year_id' => $request->fy_id]);

        return back()->with('success', 'Financial year switched for current session.');
    }
}
