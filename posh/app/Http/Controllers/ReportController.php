<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeadsByStatusExport;
use App\Exports\LeadsBySourceExport;
use App\Exports\ConvertedLeadsExport;
use App\Exports\TodayLeadsExport;
use App\Models\Deal;



class ReportController extends Controller
{
    public function __construct()
    {
        // Middleware can be applied here if needed
        //  $this->middleware(\App\Http\Middleware\PreventHistoricalFinancialYear::class)->only([
        //     'todayLeads',
        //     'todayClosedWonDeals',

        // ]);
    }
    /**** Today Leads Report ****/
    public function todayLeads(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('todays_leads_reports_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $today = Carbon::today();
        $user = auth()->user();

        // Determine if selected financial year is historical
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        $isHistorical = $selectedFyId && $activeFy && $selectedFyId != $activeFy->id;

        // If a historical financial year is selected, do not show today's leads
        if ($isHistorical) {
            // Return the view with empty leads and a flag so UI can display a proper message
            $leads = collect();
            $historicalSelected = true;
            return view('reports.today_leads', compact('leads', 'historicalSelected'));
        }

        // Normal behaviour: show today's leads for current/active FY
        // If the user is a manager, fetch their employees
        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id)->pluck('id');
        }



        // Update query to include manager's and employees' leads
        $query = Lead::with(['customer', 'organization', 'person', 'owner'])
            ->where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id);

                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds);
                }
            })->whereDate('created_at', $today);

         if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $query->where('user_owner_id', $user->id);
        }
        $leads = $query->orderByDesc('created_at')->get();
        $historicalSelected = false;
         // Fetch category names for each lead
        $leads->transform(function ($lead) {
            $categoryIds = explode(',', $lead->category ?? '');
            $lead->category_names = \App\Models\ProductCategory::whereIn('id', $categoryIds)->pluck('category_name')->toArray();
            return $lead;
        });
        return view('reports.today_leads', compact('leads', 'historicalSelected'));
    }

    // Export today's leads to Excel
    public function exportTodayLeads(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('todays_leads_reports_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $today = Carbon::today();
        $user = auth()->user();

        // Financial year check
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        $isHistorical = $selectedFyId && $activeFy && $selectedFyId != $activeFy->id;

        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id)->pluck('id');
        }

         // If a historical financial year is selected, do not show today's leads

        if ($isHistorical) {
            $leads = collect();
        } else {
            $query = Lead::with(['customer', 'organization', 'person', 'owner'])->whereDate('created_at', $today);

            if ($user->crm_role_type === 2) { // Manager
                $query->where(function ($q) use ($user, $employeeIds) {
                    $q->where('user_owner_id', $user->id);

                    if ($employeeIds->isNotEmpty()) {
                        $q->orWhereIn('user_owner_id', $employeeIds);
                    }
                });
            }

            if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
                $query->where('user_owner_id', $user->id);
            }
            $leads = $query->orderByDesc('created_at')->get();

        }
         // Fetch category names for each lead
        $leads->transform(function ($lead) {
            $categoryIds = explode(',', $lead->category ?? '');
            $lead->category_names = \App\Models\ProductCategory::whereIn('id', $categoryIds)->pluck('category_name')->toArray();
            return $lead;
        });

        $rows = $leads->map(function($lead) {
            $categoryList = '';
            if(!empty($lead->category_names))
            {
                $categoryList = implode(', ', $lead->category_names);
            }
            else
            {
                $categoryList = 'N/A';
            }
            return [
                $lead->title,
                // optional($lead->customer)->name ?? '-',
                optional($lead->organization)->name ?? '-',
                $lead->person ? trim($lead->person->first_name . ' ' . $lead->person->last_name) : '-',
                optional($lead->owner)->name ?? '-',
                $categoryList,
                $lead->status ?? '-',

                $lead->created_at ? $lead->created_at->format('d-m-Y H:i') : '',
            ];
        })->toArray();

        $export = new TodayLeadsExport($rows);
        $fileName = 'today_leads_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $fileName);
    }

    /*** Today Closed Won Deals Report ***/
    public function todayClosedWonDeals(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('today_deals_reports_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $today = Carbon::today();
        $user = auth()->user();


        // Determine if selected financial year is historical
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        $isHistorical = $selectedFyId && $activeFy && $selectedFyId != $activeFy->id;

        // If a historical financial year is selected, do not show today's closed won deals
        if ($isHistorical) {
            $deals = collect();
            $total = 0;
            $historicalSelected = true;
            return view('reports.today_closed_won_deals', compact('deals', 'total', 'historicalSelected'));
        }

        // Normal behaviour: show today's closed won deals for current/active FY
        // Show deals which were closed (close_date) today and are Closed Won
        $query = \App\Models\Deal::with(['organization', 'owner', 'dealSource'])->whereDate('close_date', $today)->where('stage', 'Closed Won');
        if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $query->where('user_owner_id', $user->id);
        }

        // Compute total across all matching deals (not just the current page)
        $total = (clone $query)->get()->sum(function($d){ return is_numeric($d->amount) ? (float)$d->amount : 0; });

        // Paginate the result for the UI to match other reports
        $perPage = 15;
        $deals = $query->orderByDesc('created_at')->paginate($perPage)->appends(request()->query());
        $historicalSelected = false;
        return view('reports.today_closed_won_deals', compact('deals', 'total', 'historicalSelected'));
    }

    // Export Today's Closed Won Deals to Excel (respects owner restriction & FY historical guard)
    public function exportTodayClosedWonDeals(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('today_deals_reports_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $today = Carbon::today();
        $user = auth()->user();

        // Determine if selected financial year is historical
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        $isHistorical = $selectedFyId && $activeFy && $selectedFyId != $activeFy->id;

        if ($isHistorical) {
            $deals = collect();
        } else {
            $query = \App\Models\Deal::with(['organization', 'owner', 'dealSource'])->whereDate('close_date', $today)->where('stage', 'Closed Won');
            if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
                $query->where('user_owner_id', $user->id);
            }
            $deals = $query->orderByDesc('created_at')->get();
        }

        $rows = $deals->map(function($d) {
            return [
                $d->title,
                $d->organization ? $d->organization->name : '-',
                $d->owner ? $d->owner->name : '-',
                $d->dealSource ? $d->dealSource->name : '-',
                is_numeric($d->amount) ? \App\Helpers\MoneyFormatter::format($d->amount) : ($d->amount ?: '-'),
                $d->close_date ? Carbon::parse($d->close_date)->format('d-m-Y') : '',
            ];
        })->toArray();

        $export = new \App\Exports\TodayClosedWonDealsExport($rows);
        $fileName = 'today_closed_won_deals_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $fileName);
    }

    /*** Converted Leads Report ***/
    public function convertedLeads(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('converted_leads_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $user = auth()->user();

        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id)->pluck('id');
        }
        // If a financial year is selected (historical or current active), restrict to that FY's converted_at range
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        // Prefer explicitly selected FY; otherwise fall back to active FY when available
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }

        $query = \App\Models\Lead::with(['customer', 'organization', 'person', 'owner'])
            ->whereNotNull('converted_at');
        // If the user is a manager, include their employees' leads
        if ($user->crm_role_type === 2) { // Manager
            $query->where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id);

                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds);
                }
            });
        }

        if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $query->where('user_owner_id', $user->id);
        }
        // If we have a financial year (selected or active), filter converted leads to that FY
        if ($fy) {
            $start = Carbon::parse($fy->from_date)->startOfDay();
            $end = Carbon::parse($fy->to_date)->endOfDay();
            $query->whereBetween('converted_at', [$start, $end]);
        }
        $perPage = 15;
        $leads = $query->orderByDesc('converted_at')->paginate($perPage)->appends(request()->query());
        // Fetch category names for each lead
            $leads->transform(function ($lead) {
                $categoryIds = explode(',', $lead->category ?? '');
                $lead->category_names = \App\Models\ProductCategory::whereIn('id', $categoryIds)->pluck('category_name')->toArray();
                return $lead;
            });

        return view('reports.converted_leads', compact('leads'));
    }

    // Export converted leads report
    public function exportConvertedLeads(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('converted_leads_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $user = auth()->user();

        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id)->pluck('id');
        }
        // Determine FY context
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }

        $query = Lead::with(['customer', 'organization', 'person', 'owner'])->whereNotNull('converted_at');

        if ($user->crm_role_type === 2) { // Manager
            $query->where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id);

                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds);
                }
            });
        }
        // Owner restriction for employees
        if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $query->where('user_owner_id', $user->id);
        }

        // If we have a financial year, filter converted_at to that FY range
        if ($fy) {
            $start = Carbon::parse($fy->from_date)->startOfDay();
            $end = Carbon::parse($fy->to_date)->endOfDay();
            $query->whereBetween('converted_at', [$start, $end]);
        }

        $leads = $query->orderByDesc('converted_at')->get();

         // Fetch category names for each lead
        $leads->transform(function ($lead) {
            $categoryIds = explode(',', $lead->category ?? '');
            $lead->category_names = \App\Models\ProductCategory::whereIn('id', $categoryIds)->pluck('category_name')->toArray();
            return $lead;
        });

        $rows = $leads->map(function($lead) {
            $categoryList = '';
            if(!empty($lead->category_names))
            {
                $categoryList = implode(', ', $lead->category_names);
            }
            else
            {
                $categoryList = 'N/A';
            }
            return [
                $lead->title,
                // optional($lead->customer)->name ?? '-',
                $categoryList,
                optional($lead->organization)->name ?? '-',
                $lead->person ? trim($lead->person->first_name . ' ' . $lead->person->last_name) : '-',
                optional($lead->owner)->name ?? '-',
                $lead->status ?? '-',
                $lead->converted_at ? Carbon::parse($lead->converted_at)->format('d-m-Y H:i') : '',
            ];
        })->toArray();

        $export = new ConvertedLeadsExport($rows);
        $fileName = 'converted_leads_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $fileName);
    }

    /*** Leads by Status Report ***/
    public function leadsByStatus(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('leads_by_status_guard')) {
            abort(403, 'Unauthorized action.');
        }
        // Get all statuses from lead_status table
        $statuses = \App\Models\LeadStatus::orderBy('name')->pluck('name');
        if ($statuses === null || $statuses->isEmpty()) {
            $statuses = collect(['No Statuses Available']); // Default fallback
        }
        $user = auth()->user();

        $query = Lead::with(['customer', 'organization', 'person', 'owner']);
        // If a historical financial year is selected, restrict to that FY's created_at range
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        $isHistorical = $selectedFyId && (! $activeFy || $selectedFyId != $activeFy->id);
        // Prefer explicitly selected FY; otherwise fall back to active FY when available
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id)->pluck('id');
        }

        if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $query->where('user_owner_id', $user->id);
        }

        if ($user->crm_role_type === 2) { // Manager
            $query->where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id);

                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds);
                }
            });
        }

        // If we have a financial year (selected or active), filter converted leads to that FY
        if ($fy) {
            $start = Carbon::parse($fy->from_date)->startOfDay();
            $end = Carbon::parse($fy->to_date)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }
        $perPage = 15;
        $leads = $query->orderByDesc('created_at')->paginate($perPage)->appends(request()->query());
        // Fetch category names for each lead
            $leads->transform(function ($lead) {
                $categoryIds = explode(',', $lead->category ?? '');
                $lead->category_names = \App\Models\ProductCategory::whereIn('id', $categoryIds)->pluck('category_name')->toArray();
                return $lead;
            });
        return view('reports.leads_by_status', compact('leads', 'statuses'));
    }

    // Export Leads by Status respecting status filter and FY context
    public function exportLeadsByStatus(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('leads_by_status_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();

        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id)->pluck('id');
        }
        // Determine financial year context
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }
        $isHistorical = $selectedFyId && $activeFy && $selectedFyId != $activeFy->id;


        $query = Lead::with(['customer', 'organization', 'person', 'owner']);

        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($user->crm_role_type === 2) { // Manager
            $query->where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id);

                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds);
                }
            });
        }

        // Owner restriction for employees
        if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $query->where('user_owner_id', $user->id);
        }

        // Apply FY range if present
        if ($fy) {
            $start = Carbon::parse($fy->from_date)->startOfDay();
            $end = Carbon::parse($fy->to_date)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }

        // If a historical FY is selected and user asked for short-relative filters it is handled in view by returning empty data.
        // For export, we'll export whatever the query yields (which will be empty for historical restricted cases).

        $leads = $query->orderByDesc('created_at')->get();
 // Fetch category names for each lead
        $leads->transform(function ($lead) {
            $categoryIds = explode(',', $lead->category ?? '');
            $lead->category_names = \App\Models\ProductCategory::whereIn('id', $categoryIds)->pluck('category_name')->toArray();
            return $lead;
        });

        $rows = $leads->map(function($lead) {
             $categoryList = '';
            if(!empty($lead->category_names))
            {
                $categoryList = implode(', ', $lead->category_names);
            }
            else
            {
                $categoryList = 'N/A';
            }
            return [
                $lead->title,
                // optional($lead->customer)->name ?? '-',
                optional($lead->organization)->name ?? '-',
                $lead->person ? trim($lead->person->first_name . ' ' . $lead->person->last_name) : '-',
                optional($lead->owner)->name ?? '-',
                $categoryList,
                $lead->status ?? '-',
                $lead->created_at ? $lead->created_at->format('d-m-Y H:i') : '',
            ];
        })->toArray();

        $export = new LeadsByStatusExport($rows);
        $fileName = 'leads_by_status_' . ($request->status ? strtolower(str_replace(' ', '_', $request->status)) . '_' : '') . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $fileName);
    }

    /*** Open Deals Report ***/
    public function openDeals(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('open_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $user = auth()->user();
        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id)->pluck('id');
        }
        $query = \App\Models\Deal::with(['organization', 'person', 'owner'])
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost']);

        if ($user->crm_role_type === 2) { // Manager
            $query->where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id);
                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds);
                }
            });
        }

        if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $query->where('user_owner_id', $user->id);
        }
        // If a historical FY is selected, restrict to deals created within that FY
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        $isHistorical = $selectedFyId && (! $activeFy || $selectedFyId != $activeFy->id);
        // $fy = $isHistorical ? \App\Models\FinancialYear::find($selectedFyId) : null;

         // Prefer explicitly selected FY; otherwise fall back to active FY when available
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }
        if ($isHistorical && $fy) {
            $start = Carbon::parse($fy->from_date)->startOfDay();
            $end = Carbon::parse($fy->to_date)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }
        $deals = $query->orderByDesc('created_at')->get();
        return view('reports.open_deals', compact('deals'));
    }

     /*** Lost Deals Report ***/
    public function lostDeals(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('lost_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $user = auth()->user();
        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id)->pluck('id');
        }
        $query = \App\Models\Deal::with(['organization', 'owner', 'dealSource'])
            ->where('stage', 'Closed Lost');
        if ($user->crm_role_type === 2) { // Manager
            $query->where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id);
                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds);
                }
            });
        }
        if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $query->where('user_owner_id', $user->id);
        }
        // If a historical FY is selected, restrict to deals created within that FY
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        $isHistorical = $selectedFyId && (! $activeFy || $selectedFyId != $activeFy->id);
        // $fy = $isHistorical ? \App\Models\FinancialYear::find($selectedFyId) : null;

         // Prefer explicitly selected FY; otherwise fall back to active FY when available
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }
        if ($fy) {
            $start = Carbon::parse($fy->from_date)->startOfDay();
            $end = Carbon::parse($fy->to_date)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }
        $deals = $query->orderByDesc('created_at')->get();
        return view('reports.lost_deals', compact('deals'));
    }

     /*** Deals Closing This Month Report ***/
    public function dealsThisMonth(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('deals_closing_this_month_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        $user = auth()->user();

        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id  )->pluck('id');
        }
        // Determine if selected financial year is historical
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        $isHistorical = $selectedFyId && $activeFy && $selectedFyId != $activeFy->id;

        // If a historical financial year is selected, do not show this month's deals
        if ($isHistorical) {
            $deals = collect();
            $historicalSelected = true;
            return view('reports.deals_this_month', compact('deals', 'historicalSelected'));
        }

        // Normal behaviour: show deals closing this month for current/active FY
        $query = \App\Models\Deal::with(['organization', 'owner'])
            ->whereBetween('close_date', [$start, $end]);
        if ($user->crm_role_type === 2) { // Manager
            $query->where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id);
                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds);
                }
            });
        }
        if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $query->where('user_owner_id', $user->id);
        }
        $deals = $query->orderByDesc('close_date')->get();
        $historicalSelected = false;
        return view('reports.deals_this_month', compact('deals', 'historicalSelected'));
    }

     /*** Revenue by Month Report ***/
    public function revenueByMonth(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('revenue_analytics_reports_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $filter = $request->get('filter', 'this_month');
        $user = auth()->user();
        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id)->pluck('id');
        }
        $baseQuery = \App\Models\Deal::query()->where('stage', 'Closed Won');
        if ($user->crm_role_type === 2) { // Manager
            $baseQuery->where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id);
                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds);
                }
            });
        }
        // Only show own records for employees
        if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $baseQuery->where('user_owner_id', $user->id);
        }
        // Filter by close_date
        // Determine financial year selection to allow FY-aware defaults
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }

        // Initialize start/end from request; may be overridden by FY logic
        $start = $request->get('start_date');
        $end = $request->get('end_date');

        // Determine if selected financial year is historical
        $isHistorical = $selectedFyId && $activeFy && $selectedFyId != $activeFy->id;

        // If a historical financial year is selected, do not process short-relative filters
        if ($isHistorical && in_array($filter, ['today', 'last_7_days', 'this_month', 'last_month'])) {
            $monthlyRevenue = collect();
            $historicalSelected = true;
            // still pass start/end (may be null) and selectedFyId for UI
            return view('reports.revenue_by_month', compact('monthlyRevenue', 'start', 'end', 'selectedFyId', 'historicalSelected'));
        }

        if ($filter === 'all') {
            // If a financial year is selected, limit 'all' to that FY range only
            if ($fy) {
                $start = Carbon::parse($fy->from_date)->startOfDay()->toDateString();
                $end = Carbon::parse($fy->to_date)->endOfDay()->toDateString();
                $baseQuery->whereDate('close_date', '>=', $start)
                          ->whereDate('close_date', '<=', $end);
            } else {
                // No FY selected: show all closed won deals across years
            }
        } elseif ($filter === 'today') {
            $baseQuery->whereDate('close_date', now());
        } elseif ($filter === 'last_7_days') {
            $baseQuery->where('close_date', '>=', now()->subDays(7));
        } elseif ($filter === 'this_month') {
            $baseQuery->whereMonth('close_date', now()->month)
                  ->whereYear('close_date', now()->year);
        } elseif ($filter === 'last_month') {
            $baseQuery->whereMonth('close_date', now()->subMonth()->month)
                  ->whereYear('close_date', now()->subMonth()->year);
        } elseif ($filter === 'this_year') {
            // Use selected/active financial year as the 'this_year' period when available
            if ($fy) {
                $start = Carbon::parse($fy->from_date)->startOfDay()->toDateString();
                $end = Carbon::parse($fy->to_date)->endOfDay()->toDateString();
                $baseQuery->whereDate('close_date', '>=', $start)
                          ->whereDate('close_date', '<=', $end);
            } else {
                $baseQuery->whereYear('close_date', now()->year);
            }
        } elseif ($filter === 'last_year') {
            // When FY selected, consider 'last_year' relative to calendar year; keep default behaviour
            $baseQuery->whereYear('close_date', now()->subYear()->year);
        } elseif ($filter === 'between') {
            $start = $request->get('start_date');
            $end = $request->get('end_date');
            // If dates are not provided and we have a selected FY, use FY range
            if ((!$start || !$end) && $fy) {
                $start = Carbon::parse($fy->from_date)->startOfDay()->toDateString();
                $end = Carbon::parse($fy->to_date)->endOfDay()->toDateString();
            }
            if ($start && $end) {
                $baseQuery->whereDate('close_date', '>=', $start)
                          ->whereDate('close_date', '<=', $end);
            }
        }

        // AJAX: return deals for selected month
        if ($request->ajax() || $request->get('ajax')) {
            $month = $request->get('month'); // e.g. 'Aug 2025'
            $deals = (clone $baseQuery)
                ->whereRaw("DATE_FORMAT(close_date, '%b %Y') = ?", [$month])
                ->with(['person', 'organization'])
                ->orderBy('close_date')
                ->get();
            $result = $deals->map(function($deal) {
                return [
                    'title' => $deal->title,
                    'company' => $deal->organization ? $deal->organization->name : '-',
                    'contact_person' => $deal->person ? ($deal->person->first_name . ' ' . $deal->person->last_name) : '-',
                    'stage' => $deal->stage,
                    'amount' => $deal->amount,
                ];
            });
            return response()->json($result);
        }

        $deals = (clone $baseQuery)->orderBy('close_date')->get();

        // Group by month and year
        $monthlyRevenue = collect();
        $grouped = $deals->groupBy(function($deal) {
            return Carbon::parse($deal->close_date)->format('M Y');
        });
        foreach ($grouped as $month => $items) {
            $monthlyRevenue->push([
                'month' => $month,
                'amount' => $items->sum('amount'),
            ]);
        }

        // Sort by date ascending
        $monthlyRevenue = $monthlyRevenue->sortBy(function($row) {
            return Carbon::parse('01 ' . $row['month']);
        })->values();

        // Pass start/end and selected FY id so the view can limit navigation/calendar UI
        return view('reports.revenue_by_month', compact('monthlyRevenue', 'start', 'end', 'selectedFyId'));
    }

    /*** Deals Report ***/
    public function deals(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('deals_analytics_reports_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $filter = $request->get('filter', 'today');
        $stageFilter = $request->get('stage', '');
        $user = auth()->user();
        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id )->pluck('id');
        }

        $baseQuery = \App\Models\Deal::query();
        // Financial year context
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }
        $isHistorical = $selectedFyId && $activeFy && $selectedFyId != $activeFy->id;
        // start/end for view defaults
        $start = $request->get('start_date');
        $end = $request->get('end_date');

        if ($user->crm_role_type === 2) { // Manager
            $baseQuery->where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id);
                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds);
                }
            });
        }
        // Only show own records for employees
        if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $baseQuery->where('user_owner_id', $user->id);
        }
    // removed extra closing brace
        // If a historical FY is selected, do not process short-relative filters
        if ($isHistorical && in_array($filter, ['today', 'last_week', 'this_month', 'last_month'])) {
            $perPage = 15;
            $deals = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage, 1, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);

            $dealSourceLabels = [];
            $dealSourceCounts = [];
            $dealStageLabels = [];
            $dealStageCounts = [];
            $historicalSelected = true;

            return view('reports.deals', compact('deals', 'dealSourceLabels', 'dealSourceCounts', 'dealStageLabels', 'dealStageCounts', 'start', 'end', 'historicalSelected'));
        }

        if ($filter === 'today') {
            // If user asked for Closed Won deals for today, use close_date (deal closed today)
            if ($stageFilter === 'Closed Won') {
                $baseQuery->whereDate('close_date', now());
            } else {
                $baseQuery->whereDate('created_at', now());
            }
        } elseif ($filter === 'last_week') {
            $baseQuery->where('created_at', '>=', now()->subWeek());
        } elseif ($filter === 'this_month') {
            $baseQuery->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        } elseif ($filter === 'last_month') {
            $baseQuery->whereMonth('created_at', now()->subMonth()->month)
                  ->whereYear('created_at', now()->subMonth()->year);
        } elseif ($filter === 'yearly') {
            // Use selected/active FY when available for yearly analytics
            if ($fy) {
                $start = Carbon::parse($fy->from_date)->startOfDay()->toDateString();
                $end = Carbon::parse($fy->to_date)->endOfDay()->toDateString();
                $baseQuery->whereDate('created_at', '>=', $start)
                          ->whereDate('created_at', '<=', $end);
            } else {
                $baseQuery->whereYear('created_at', now()->year);
            }
        } elseif ($filter === 'between') {
            // prefer request values; if missing and we have FY, use the FY range
            $start = $request->get('start_date');
            $end = $request->get('end_date');
            if ((!$start || !$end) && $fy) {
                $start = Carbon::parse($fy->from_date)->startOfDay()->toDateString();
                $end = Carbon::parse($fy->to_date)->endOfDay()->toDateString();
            }
            if ($start && $end) {
                $baseQuery->whereDate('created_at', '>=', $start)
                          ->whereDate('created_at', '<=', $end);
            }
        }
    // removed extra closing brace
        if ($stageFilter) {
            if ($stageFilter === 'Open') {
                $baseQuery->whereNotIn('stage', ['Closed Won', 'Closed Lost']);
            } else {
                $baseQuery->where('stage', $stageFilter);
            }
        }

        $deals = (clone $baseQuery)->orderByDesc('created_at')->with(['organization', 'person', 'owner', 'dealSource'])->paginate(15);

        // For analytics, get all filtered records (not paginated)
        $allDeals = (clone $baseQuery)->with(['dealSource'])->get();

        // Deal source analytics
        $dealSourceData = $allDeals->groupBy(function($deal) {
            return $deal->dealSource->name ?? 'Unknown';
        });
        $dealSourceLabels = $dealSourceData->keys()->toArray();
        $dealSourceCounts = $dealSourceData->map->count()->values()->toArray();

        // Deal stage analytics
        $dealStageData = $allDeals->groupBy('stage');
        $dealStageLabels = $dealStageData->keys()->toArray();
        $dealStageCounts = $dealStageData->map->count()->values()->toArray();

        $historicalSelected = false;
        return view('reports.deals', compact('deals', 'dealSourceLabels', 'dealSourceCounts', 'dealStageLabels', 'dealStageCounts', 'start', 'end', 'historicalSelected'));
    }

     /*** Leads Report ***/
    public function leads(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('leads_analytics_report_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $filter = $request->get('filter', 'today');
        $user = auth()->user();
        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id )->pluck('id');
        }
        $baseQuery = Lead::query();
        if ($user->crm_role_type === 2) { // Manager
            $baseQuery->where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id);
                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds);
                }
            });
        }
        // Only show own records for employees
        if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $baseQuery->where('user_owner_id', $user->id);
        }

        // Apply financial year concept: prefer selected financial year (if any), otherwise use active FY
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }

        // Determine if selected financial year is historical (different from active)
        $isHistorical = $selectedFyId && $activeFy && $selectedFyId != $activeFy->id;

        // If a historical FY is selected and the user asked for a short relative period
        // (today, last_week, this_month, last_month), we don't process those reports for
        // historical years — return an empty paginator and a flag for the view.
        if ($isHistorical && in_array($filter, ['today', 'last_week', 'this_month', 'last_month'])) {
            $perPage = 15;
            $leads = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage, 1, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);

            $leadSourceLabels = [];
            $leadSourceCounts = [];
            $leadStatusLabels = [];
            $leadStatusCounts = [];
            $historicalSelected = true;

            return view('reports.leads', compact('leads', 'leadSourceLabels', 'leadSourceCounts', 'leadStatusLabels', 'leadStatusCounts', 'historicalSelected'));
        }

        // Period filters (FY-aware)
        // Initialize start/end for the view (may be populated from FY or request)
        $start = $request->get('start_date');
        $end = $request->get('end_date');
        if ($filter === 'today') {
            $baseQuery->whereDate('created_at', now());
        } elseif ($filter === 'last_week') {
            $baseQuery->where('created_at', '>=', now()->subWeek());
        } elseif ($filter === 'this_month') {
            $baseQuery->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        } elseif ($filter === 'last_month') {
            $baseQuery->whereMonth('created_at', now()->subMonth()->month)
                  ->whereYear('created_at', now()->subMonth()->year);
        } elseif ($filter === 'yearly') {
            // For analytics 'yearly' use the selected financial year when available, otherwise use calendar year
            if ($fy) {
                $start = Carbon::parse($fy->from_date)->startOfDay()->toDateString();
                $end = Carbon::parse($fy->to_date)->endOfDay()->toDateString();
                $baseQuery->whereDate('created_at', '>=', $start)
                          ->whereDate('created_at', '<=', $end);
            } else {
                $baseQuery->whereYear('created_at', now()->year);
            }
        } elseif ($filter === 'between') {
            // prefer request values; if missing and we have an FY, use its range
            $start = $request->get('start_date');
            $end = $request->get('end_date');
            if ((!$start || !$end) && $fy) {
                $start = Carbon::parse($fy->from_date)->startOfDay()->toDateString();
                $end = Carbon::parse($fy->to_date)->endOfDay()->toDateString();
            }
            if ($start && $end) {
                $baseQuery->whereDate('created_at', '>=', $start)
                          ->whereDate('created_at', '<=', $end);
            }
        }
        $leads = (clone $baseQuery)->orderByDesc('created_at')->with(['customer', 'organization', 'person', 'owner', 'leadSource'])->paginate(15);

        // For analytics, get all filtered records (not paginated)
        $allLeads = (clone $baseQuery)->with(['leadSource'])->get();

        // Lead source analytics
        $leadSourceData = $allLeads->groupBy(function($lead) {
            return $lead->leadSource->name ?? 'Unknown';
        });
        $leadSourceLabels = $leadSourceData->keys()->toArray();
        $leadSourceCounts = $leadSourceData->map->count()->values()->toArray();

        // Lead status analytics
        $leadStatusData = $allLeads->groupBy('status');
        $leadStatusLabels = $leadStatusData->keys()->toArray();
        $leadStatusCounts = $leadStatusData->map->count()->values()->toArray();

        // pass start/end so the view can show the effective date range (including FY fallback)
        return view('reports.leads', compact('leads', 'leadSourceLabels', 'leadSourceCounts', 'leadStatusLabels', 'leadStatusCounts', 'start', 'end'));
    }

    /*** Leads By Source Report (custom) ***/
    public function leadsBySource(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('leads_by_source_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $start = $request->get('start_date');
        $end = $request->get('end_date');
        // If no explicit start/end provided and a historical FY is selected, use FY range
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        $isHistorical = $selectedFyId && (! $activeFy || $selectedFyId != $activeFy->id);
        // $fy = $isHistorical ? \App\Models\FinancialYear::find($selectedFyId) : null;
        // Prefer explicitly selected FY; otherwise fall back to active FY when available
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }
        if ((!$start || !$end)  && $fy) {
            $start = Carbon::parse($fy->from_date)->startOfDay()->toDateString();
            $end = Carbon::parse($fy->to_date)->endOfDay()->toDateString();
        }
        $user = auth()->user();
        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id)->pluck('id');
        }
        $query = Lead::with(['leadSource', 'organization', 'person', 'owner']);
        if ($start && $end) {
            $query->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
        }
        if ($user->crm_role_type === 2) { // Manager
            $query->where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id);

                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds);
                }
            });
        }
        if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $query->where('user_owner_id', $user->id);
        }

        // Paginate leads and group only the current page results by lead source
        $perPage = 15;
        $leadsPaginator = $query->orderBy('lead_source')->orderByDesc('created_at')->paginate($perPage)->appends(request()->query());
        // Fetch category names for each lead
        $leadsPaginator->transform(function ($lead) {
            $categoryIds = explode(',', $lead->category ?? '');
            $lead->category_names = \App\Models\ProductCategory::whereIn('id', $categoryIds)->pluck('category_name')->toArray();
            return $lead;
        });
        $currentPageLeads = $leadsPaginator->getCollection();

        // Group current page leads by lead source for display
        $grouped = $currentPageLeads->groupBy(function($lead) {
            return $lead->leadSource->name ?? '-';
        });

        return view('reports.leads_by_source', compact('grouped', 'start', 'end', 'leadsPaginator'));
    }

    // Export Leads by Source respecting start/end date and FY context
    public function exportLeadsBySource(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('leads_by_source_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();

        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id)->pluck('id');
        }

        // Determine FY context
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }


        $query = Lead::with(['customer', 'organization', 'person', 'owner', 'leadSource']);

        // Date filters: use start/end from request, fallback to FY if not provided
        $start = $request->get('start_date');
        $end = $request->get('end_date');
        if ((!$start || !$end) && $fy) {
            $start = Carbon::parse($fy->from_date)->startOfDay()->toDateString();
            $end = Carbon::parse($fy->to_date)->endOfDay()->toDateString();
        }
        if ($start && $end) {
            $query->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
        }

        if ($user->crm_role_type === 2) { // Manager
            $query->where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id);

                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds);
                }
            });
        }
        // Owner restriction for employees
        if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $query->where('user_owner_id', $user->id);
        }

        // Build and fetch results
        $leads = $query->orderByDesc('created_at')->get();

         // Fetch category names for each lead
        $leads->transform(function ($lead) {
            $categoryIds = explode(',', $lead->category ?? '');
            $lead->category_names = \App\Models\ProductCategory::whereIn('id', $categoryIds)->pluck('category_name')->toArray();
            return $lead;
        });

        // Map to rows grouped by lead source; since view groups per page, for export we'll include source name on each row
        $rows = $leads->map(function($lead) {
             $categoryList = '';
            if(!empty($lead->category_names))
            {
                $categoryList = implode(', ', $lead->category_names);
            }
            else
            {
                $categoryList = 'N/A';
            }
            return [
                optional($lead->leadSource)->name ?? '-',
                $lead->person ? trim(($lead->person->first_name ?? '') . ' ' . ($lead->person->last_name ?? '')) : '-',
                $categoryList,
                $lead->person && ($lead->person->mobile ?? '') ? $lead->person->mobile : ($lead->mobile ?? '-'),
                $lead->organization ? $lead->organization->name : '-',
                $lead->title,
                $lead->created_at ? $lead->created_at->format('d-m-Y h:i A') : '-',
                $lead->owner ? $lead->owner->name : '-',
            ];
        })->toArray();

        $export = new LeadsBySourceExport($rows);
        $fileName = 'leads_by_source_' . ($start ? $start . '_' : '') . ($end ? $end . '_' : '') . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $fileName);
    }

    /*** Deals By Source Report (custom) ***/
    public function dealsBySource(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('deal_by_source_reports_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $start = $request->get('start_date');
        $end = $request->get('end_date');
        // If no explicit start/end provided and a historical FY is selected, use FY range
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        $isHistorical = $selectedFyId && (! $activeFy || $selectedFyId != $activeFy->id);
        // $fy = $isHistorical ? \App\Models\FinancialYear::find($selectedFyId) : null;
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }
        if ((!$start || !$end) &&  $fy) {
            $start = Carbon::parse($fy->from_date)->startOfDay()->toDateString();
            $end = Carbon::parse($fy->to_date)->endOfDay()->toDateString();
        }
        $user = auth()->user();
        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id)->pluck('id');
        }
        $query = \App\Models\Deal::with(['dealSource', 'organization', 'person', 'owner']);
        // If start/end provided, filter by created_at
        if ($start && $end) {
            $query->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
        }
        if ($user->crm_role_type === 2) { // Manager
            $query->where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id);

                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds);
                }
            });
        }
        // Owner restriction for employees
        if (in_array($user->crm_role_type, [3, '3', 'employee'])) {
            $query->where('user_owner_id', $user->id);
        }
        $deals = $query->orderBy('lead_source')->orderByDesc('created_at')->get();

        // Group deals by deal source
        $grouped = $deals->groupBy(function($deal) {
            return $deal->dealSource->name ?? '-';
        });

        return view('reports.deals_by_source', compact('grouped', 'start', 'end'));
    }

    /*** Task Report ***/
    public function taskReport(Request $request)
    {

        $today  = Carbon::today();
        $user = auth()->user();
        $employeeIds = collect();
        if ($user->crm_role_type === 2) {
            $employeeIds = \App\Models\User::where('assign_manager', $user->id  )->pluck('id');
        }

        if (in_array($user->crm_role_type, [0, 1])) {
            // Admin, Super Admin, and Manager can see all tasks
            $query = \App\Models\Task::query();
        }elseif ($user->crm_role_type === 2) {
            // Managers can see their own tasks and those of their employees
            $query = \App\Models\Task::where(function ($q) use ($user, $employeeIds) {
                $q->where('user_owner_id', $user->id)
                    ->orWhere('user_assigned_id', $user->id);

                if ($employeeIds->isNotEmpty()) {
                    $q->orWhereIn('user_owner_id', $employeeIds)
                        ->orWhereIn('user_assigned_id', $employeeIds);
                }
            });
        }else {
            // Users can only see their owned and assigned tasks
            $query = \App\Models\Task::where(function ($q) use ($user) {
                $q->where('user_owner_id', $user->id)
                  ->orWhere('user_assigned_id', $user->id);
            });
        }
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'overdue':
                    $query->where('due_at', '<', $today)
                            ->where('status', '!=', 'completed');
                    break;
                case 'today':
                    $query->whereDate('due_at', $today)
                    ->where('status', '!=', 'completed');
                    break;
                case 'upcoming':
                    $query->where('due_at', '>', $today)
                    ->where('status', '!=', 'completed');
                    break;
                case 'completed':
                    $query->where('status', 'completed');
                    break;
            }
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('due_at', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('assigned_to')) {
            $query->where('user_assigned_id', $request->assigned_to);
        }

        $tasks = $query->paginate(10);
        $tasks->getCollection()->transform(fn ($task) =>
            $this->addOrganizationName($task)
        );

        return view('reports.task_report', compact('tasks'));
    }

    function addOrganizationName($task)
    {
        try {
            if ($task->related_type === 'deal') {
                $task->organization_name =
                    Deal::with('organization')->find($task->related_id)->organization->name ?? 'No Organization';
            } elseif ($task->related_type === 'lead') {
                $task->organization_name =
                    Lead::with('organization')->find($task->related_id)->organization->name ?? 'No Organization';
            } else {
                $task->organization_name = 'Invalid Related Type';
            }
        } catch (\Exception $e) {
            $task->organization_name = 'Error Fetching Organization';
        }

        return $task;
    }
}
