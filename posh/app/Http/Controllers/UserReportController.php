<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Lead;
use App\Models\Deal;
use Barryvdh\DomPDF\Facade\Pdf;

class UserReportController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasCrmPermission('user_report_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $userId = request('user_id');
        $fromDate = request('from_date');
        $toDate = request('to_date');

        // Filter users (exclude super admin)
        $usersQuery = User::query();
        if ($userId) {
            $usersQuery->where('id', $userId);
        }
        $usersQuery->where('crm_role_type', '!=', '0'); // Exclude super admin
        $users = $usersQuery->get();

        // Filter leads
        $leadsQuery = Lead::query();
        if ($userId) {
            $leadsQuery->where('user_owner_id', $userId);
        }
        if ($fromDate) {
            $leadsQuery->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $leadsQuery->whereDate('created_at', '<=', $toDate);
        }
        $leads = $leadsQuery->get();

        // Filter deals
        $dealsQuery = Deal::query();
        if ($userId) {
            $dealsQuery->where('user_owner_id', $userId);
        }
        if ($fromDate) {
            $dealsQuery->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $dealsQuery->whereDate('created_at', '<=', $toDate);
        }
        $deals = $dealsQuery->get();

        // Get leads count per user
        $leadsCountQuery = Lead::query();
        if ($userId) {
            $leadsCountQuery->where('user_owner_id', $userId);
        }
        if ($fromDate) {
            $leadsCountQuery->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $leadsCountQuery->whereDate('created_at', '<=', $toDate);
        }
        $leadsCountQuery->whereNull('converted_at');
        $leadsCount = $leadsCountQuery->get()->groupBy('user_owner_id')->map->count();

        // Get deals count per user
        $dealsCount = $deals->groupBy('user_owner_id')->map->count();

        // Filter converted leads for count
        $convertedLeadsCountQuery = Lead::query();
        if ($userId) {
            $convertedLeadsCountQuery->where('user_owner_id', $userId);
        }
        if ($fromDate) {
            $convertedLeadsCountQuery->whereDate('converted_at', '>=', $fromDate);
        }
        if ($toDate) {
            $convertedLeadsCountQuery->whereDate('converted_at', '<=', $toDate);
        }
        $convertedLeadsCountQuery->whereNotNull('converted_at');
        $convertedLeadsCount = $convertedLeadsCountQuery->get()->groupBy('user_owner_id')->map->count();

        // Prepare report data
        $report = $users->map(function ($user) use ($leadsCount, $dealsCount, $convertedLeadsCount) {
            return [
                'user' => $user,
                'leads_count' => $leadsCount[$user->id] ?? 0,
                'converted_leads_count' => $convertedLeadsCount[$user->id] ?? 0,
                'deals_count' => $dealsCount[$user->id] ?? 0,
            ];
        });

        return view('reports.user_report', compact('report'));
    }

    public function convertedLeads($userId)
    {
         if (!auth()->user()->hasCrmPermission('user_report_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $fromDate = request('from_date');
        $toDate = request('to_date');
        $leadsQuery = \App\Models\Lead::where('user_owner_id', $userId)
            ->whereNotNull('converted_at');

        // If no explicit from/to provided, and a financial year is selected (or active exists),
        // restrict to that FY's converted_at range so the user report follows FY selection.
        if (!$fromDate && !$toDate) {
            $selectedFyId = session('selected_financial_year', null);
            $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
            if ($selectedFyId) {
                $fy = \App\Models\FinancialYear::find($selectedFyId);
            } else {
                $fy = $activeFy;
            }
            if (!empty($fy)) {
                $start = \Carbon\Carbon::parse($fy->from_date)->startOfDay()->toDateString();
                $end = \Carbon\Carbon::parse($fy->to_date)->endOfDay()->toDateString();
                $leadsQuery->whereDate('converted_at', '>=', $start)->whereDate('converted_at', '<=', $end);
            }
        }

        if ($fromDate) {
            $leadsQuery->whereDate('converted_at', '>=', $fromDate);
        }
        if ($toDate) {
            $leadsQuery->whereDate('converted_at', '<=', $toDate);
        }
        $leads = $leadsQuery->get();
        $html = '<table class="table table-striped">';
        $html .= '<thead><tr><th>Lead Title</th><th>Company Name</th><th>Status</th><th>Converted At</th></tr></thead><tbody>';
        foreach ($leads as $lead) {
            $company = $lead->organization ? $lead->organization->name : '-';
            $html .= '<tr>';
            $html .= '<td><a href="/leads/' . $lead->id . '" target="_blank">' . e($lead->title) . '</a></td>';
            $html .= '<td>' . e($company) . '</td>';
            $html .= '<td>' . e($lead->status) . '</td>';
            $html .= '<td>' . ($lead->converted_at ? $lead->converted_at->format('d-m-Y') : '-') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return response($html);
    }

    public function userDeals($userId)
    {
         if (!auth()->user()->hasCrmPermission('user_report_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $month = request('month');
        $stage = request('stage');
        $startDate = $month ? $month . '-01' : null;
        
        $endDate = $month ? date('Y-m-t', strtotime($startDate)) : null;
        $dealsQuery = \App\Models\Deal::where('user_owner_id', $userId);
        if ($startDate && $endDate) {
            if($stage === 'Closed Won') {
                $dealsQuery->whereDate('close_date', '>=', $startDate)
                          ->whereDate('close_date', '<=', $endDate);
            } else {
                $dealsQuery->whereDate('created_at', '>=', $startDate)
                          ->whereDate('created_at', '<=', $endDate);
            }
            // $dealsQuery->whereDate('created_at', '>=', $startDate)
            //           ->whereDate('created_at', '<=', $endDate);
        }
        if ($stage) {
            $dealsQuery->where('stage', $stage);
        }
        $deals = $dealsQuery->get();
        
        $html = '<table class="table table-striped">';
        $html .= '<thead><tr><th>Deal Title</th><th>Company Name</th><th>Status</th><th>Created At</th><th>Deadline At</th></tr></thead><tbody>';
        foreach ($deals as $deal) {
            $company = $deal->organization ? $deal->organization->name : '-';
            $contact = $deal->person ? trim($deal->person->first_name . ' ' . $deal->person->last_name) : '-';
            $html .= '<tr>';
            $html .= '<td><a href="/deals/' . $deal->id . '" target="_blank">' . e($deal->title) . '</a></td>';
            $html .= '<td>' . e($company) . '</td>';

            $html .= '<td>' . e($deal->status) . '</td>';
            $html .= '<td>' . ($deal->created_at ? date('d-m-Y', strtotime($deal->created_at)) : '-') . '</td>';
            $html .= '<td>' . ($deal->close_date ? date('d-m-Y', strtotime($deal->close_date)) : '-') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return response($html);
    }
    public function leadDetails($leadId)
    {
         if (!auth()->user()->hasCrmPermission('user_report_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $lead = \App\Models\Lead::findOrFail($leadId);
        $html = '<div>';
        $html .= '<h5>' . e($lead->title) . '</h5>';
        $html .= '<p><strong>Status:</strong> ' . e($lead->status) . '</p>';
        $html .= '<p><strong>Description:</strong> ' . e($lead->description) . '</p>';
        $html .= '<p><strong>Amount:</strong> ' . e($lead->amount) . '</p>';
        $html .= '<p><strong>Expected Close:</strong> ' . e($lead->expected_close) . '</p>';
        $html .= '</div>';
        return response($html);
    }
    public function userLeads($userId)
    {
         if (!auth()->user()->hasCrmPermission('user_report_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $month = request('month');
        $startDate = $month ? $month . '-01' : null;
        $endDate = $month ? date('Y-m-t', strtotime($startDate)) : null;
        $leadsQuery = \App\Models\Lead::where('user_owner_id', $userId);
        if ($startDate && $endDate) {
            $leadsQuery->whereDate('created_at', '>=', $startDate)
                      ->whereDate('created_at', '<=', $endDate);
        }
        $leads = $leadsQuery->get();
        $html = '<table class="table table-striped">';
        $html .= '<thead><tr><th>Lead Title</th><th>Company Name</th><th>Status</th><th>Created At</th></tr></thead><tbody>';
        foreach ($leads as $lead) {
            $company = $lead->organization ? $lead->organization->name : '-';
            $contact = $lead->person ? trim($lead->person->first_name . ' ' . $lead->person->last_name) : '-';
            $html .= '<tr>';
            $html .= '<td><a href="/leads/' . $lead->id . '" target="_blank">' . e($lead->title) . '</a></td>';
            $html .= '<td>' . e($company) . '</td>';
            $html .= '<td>' . e($lead->status) . '</td>';
            $html .= '<td>' . ($lead->created_at ? date('d-m-Y', strtotime($lead->created_at)) : '-') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return response($html);
    }

    public function userReports(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('user_report_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $userId = $request->input('user_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Get users (exclude super admin)
        // $usersQuery = User::query()->where('crm_role_type', '!=', '0');
        $usersQuery = User::query()->whereNotIn('crm_role_type', [0, 1]);
        if ($userId) {
            $usersQuery->where('id', $userId);
        }
        $users = $usersQuery->get();

        $results = [];

        foreach ($users as $user) {
            // Leads for user
            $leadsQuery = Lead::where('user_owner_id', $user->id);
            if ($fromDate) {
                $leadsQuery->whereDate('converted_at', '>=', $fromDate);
            }
            if ($toDate) {
                $leadsQuery->whereDate('converted_at', '<=', $toDate);
            }
            $leads = $leadsQuery->get();

            // Deals for user
            $dealsQuery = Deal::where('user_owner_id', $user->id);
            if ($fromDate) {
                $dealsQuery->whereDate('created_at', '>=', $fromDate);
            }
            if ($toDate) {
                $dealsQuery->whereDate('created_at', '<=', $toDate);
            }
            $deals = $dealsQuery->get();

            $results[] = [
                'user' => $user,
                'leads' => $leads,
                'deals' => $deals,
            ];
        }

        // You can return a view or JSON as needed
        return view('reports.user_reports_list', compact('results', 'fromDate', 'toDate', 'userId'));
    }

    public function userReportsPdf(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('user_report_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $userId = $request->input('user_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // $usersQuery = User::query()->where('crm_role_type', '!=', '0');
        $usersQuery = User::query()->whereNotIn('crm_role_type', [0, 1]);
        if ($userId) {
            $usersQuery->where('id', $userId);
        }
        $users = $usersQuery->get();

        $results = [];
        foreach ($users as $user) {
            $leadsQuery = Lead::where('user_owner_id', $user->id);
            if ($fromDate) {
                $leadsQuery->whereDate('created_at', '>=', $fromDate);
            }
            if ($toDate) {
                $leadsQuery->whereDate('created_at', '<=', $toDate);
            }
            $leads = $leadsQuery->get();

            $dealsQuery = Deal::where('user_owner_id', $user->id);
            if ($fromDate) {
                $dealsQuery->whereDate('created_at', '>=', $fromDate);
            }
            if ($toDate) {
                $dealsQuery->whereDate('created_at', '<=', $toDate);
            }
            $deals = $dealsQuery->get();

            $results[] = [
                'user' => $user,
                'leads' => $leads,
                'deals' => $deals,
            ];
        }

        $pdf = Pdf::loadView('reports.user_reports_list_pdf', compact('results', 'fromDate', 'toDate', 'userId'));
        return $pdf->download('user_reports.pdf');
    }

    public function monthlyUserReport(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('user_report_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $currentUser = auth()->user();
        $isAdminOrManager = in_array($currentUser->crm_role_type, [0, 1, 2]);

        // If regular employee, only show their own report
        $userId = $request->input('user_id');
        if (!$isAdminOrManager && !$userId) {
            $userId = $currentUser->id;
        } elseif (!$isAdminOrManager && $userId && $userId != $currentUser->id) {
            // Regular employee trying to view another employee's report
            abort(403, 'You can only view your own report.');
        }
    
        // Do not default month immediately; apply financial year semantics first
        $month = $request->input('month', null);

        // Resolve selected or active financial year
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }

        $fyStart = null;
        $fyEnd = null;
        if (!empty($fy)) {
            try {
                $fyStart = \Carbon\Carbon::parse($fy->from_date)->startOfMonth();
                $fyEnd = \Carbon\Carbon::parse($fy->to_date)->endOfMonth();
            } catch (\Exception $e) {
                $fyStart = null;
                $fyEnd = null;
            }
        }

        $selectedOutsideFy = false;

        // If no month provided, default to current month (we still validate against FY below)
        if (empty($month)) {
            $month = date('Y-m');
        } else {
            // Validate provided month format and ensure it's inside FY (if FY present)
            try {
                $mStart = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                if (!empty($fyStart) && !empty($fyEnd)) {
                    if ($mStart->lt($fyStart) || $mStart->gt($fyEnd)) {
                        $selectedOutsideFy = true;
                    }
                }
            } catch (\Exception $e) {
                // Invalid month format; fall back to FY start or current month
                if (!empty($fyStart)) {
                    $month = $fyStart->format('Y-m');
                } else {
                    $month = date('Y-m');
                }
            }
        }

        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $usersQuery = \App\Models\User::query()->where('crm_role_type', '!=', '0');
        if ($currentUser->crm_role_type === 2) {
            // If manager, only show their team members
            $usersQuery->where('assign_manager', $currentUser->id)->orWhere('id', $currentUser->id);
        } else {
            if ($userId) {
                $usersQuery->where('id', $userId);
            }
        }
        $users = $usersQuery->get();

        $results = [];
        // If the requested month is outside the selected financial year, return empty results
        if (!$selectedOutsideFy) {
            foreach ($users as $user) {
                $calls_count = \App\Models\CallLog::where('created_by', $user->id)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->count();
                
                // Total deals generated based on created_at
                $total_deals = \App\Models\Deal::where('user_owner_id', $user->id)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->count();

                // Deals closed won and total value based on close_date
                $deals = \App\Models\Deal::where('user_owner_id', $user->id)
                    ->whereDate('close_date', '>=', $startDate)
                    ->whereDate('close_date', '<=', $endDate)
                    ->get();
                $deals_won_count = $deals->where('stage', 'Closed Won')->count();
               
                $deal_amount = $deals->where('stage', 'Closed Won')->sum('amount');

                $leads_count = \App\Models\Lead::where('user_owner_id', $user->id)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->count();

                $deal_details = \App\Models\Deal::where('user_owner_id', $user->id)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->get(); // Ensure deals_count is calculated
                $deals_count = $deal_details->count();
                $deals_lost_count = $deal_details->where('stage', 'Closed Lost')->count();
                $results[] = [
                    'month' => date('F Y', strtotime($startDate)),
                    'user' => $user,
                    'calls_count' => $calls_count,
                    'total_deals' => $total_deals, // Total deals generated
                    'deals_count' => $deals_count, // Add deals_count to results
                    'deals_won_count' => $deals_won_count,
                    'deals_lost_count' => $deals_lost_count,
                    'deal_amount' => $deal_amount,
                    'leads_count' => $leads_count,
                ];
            }
        }

        return view('reports.monthly_user_report', compact('results', 'month', 'userId', 'startDate', 'endDate', 'selectedOutsideFy', 'selectedFyId'));
    }

    public function exportMonthlyUserReportExcel(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('user_report_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $currentUser = auth()->user();
        $isAdminOrManager = in_array($currentUser->crm_role_type, [0, 1, 2]);

        // If regular employee, only show their own report
        $userId = $request->input('user_id');
        if (!$isAdminOrManager && !$userId) {
            $userId = $currentUser->id;
        } elseif (!$isAdminOrManager && $userId && $userId != $currentUser->id) {
            // Regular employee trying to view another employee's report
            abort(403, 'You can only view your own report.');
        }
    
        // Do not default month immediately; apply financial year semantics first
        $month = $request->input('month', null);

        // Resolve selected or active financial year
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }

        $fyStart = null;
        $fyEnd = null;
        if (!empty($fy)) {
            try {
                $fyStart = \Carbon\Carbon::parse($fy->from_date)->startOfMonth();
                $fyEnd = \Carbon\Carbon::parse($fy->to_date)->endOfMonth();
            } catch (\Exception $e) {
                $fyStart = null;
                $fyEnd = null;
            }
        }

        $selectedOutsideFy = false;

        // If no month provided, default to current month (we still validate against FY below)
        if (empty($month)) {
            $month = date('Y-m');
        } else {
            // Validate provided month format and ensure it's inside FY (if FY present)
            try {
                $mStart = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                if (!empty($fyStart) && !empty($fyEnd)) {
                    if ($mStart->lt($fyStart) || $mStart->gt($fyEnd)) {
                        $selectedOutsideFy = true;
                    }
                }
            } catch (\Exception $e) {
                // Invalid month format; fall back to FY start or current month
                if (!empty($fyStart)) {
                    $month = $fyStart->format('Y-m');
                } else {
                    $month = date('Y-m');
                }
            }
        }

        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $usersQuery = \App\Models\User::query()->where('crm_role_type', '!=', '0');
        if ($currentUser->crm_role_type === 2) {
            // If manager, only show their team members
            $usersQuery->where('assign_manager', $currentUser->id)->orWhere('id', $currentUser->id);
        } else {
            if ($userId) {
                $usersQuery->where('id', $userId);
            }
        }
        $users = $usersQuery->get();

        $results = [];
        // If the requested month is outside the selected financial year, return empty results
        if (!$selectedOutsideFy) {
            foreach ($users as $user) {
                $calls_count = \App\Models\CallLog::where('created_by', $user->id)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->count();
                
                // Total deals generated based on created_at
                $total_deals = \App\Models\Deal::where('user_owner_id', $user->id)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->count();

                // Deals closed won and total value based on close_date
                $deals = \App\Models\Deal::where('user_owner_id', $user->id)
                    ->whereDate('close_date', '>=', $startDate)
                    ->whereDate('close_date', '<=', $endDate)
                    ->get();
                $deals_won_count = $deals->where('stage', 'Closed Won')->count();
               
                $deal_amount = $deals->where('stage', 'Closed Won')->sum('amount');

                $leads_count = \App\Models\Lead::where('user_owner_id', $user->id)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->count();

                $deal_details = \App\Models\Deal::where('user_owner_id', $user->id)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->get(); // Ensure deals_count is calculated
                $deals_count = $deal_details->count();
                $deals_lost_count = $deal_details->where('stage', 'Closed Lost')->count();
                $results[] = [
                    'month' => date('F Y', strtotime($startDate)),
                    'user' => $user,
                    'calls_count' => $calls_count,
                    'total_deals' => $total_deals, // Total deals generated
                    'deals_count' => $deals_count, // Add deals_count to results
                    'deals_won_count' => $deals_won_count,
                    'deals_lost_count' => $deals_lost_count,
                    'deal_amount' => $deal_amount,
                    'leads_count' => $leads_count,
                ];
            }
        }

        $pdf = Pdf::loadView('reports.monthly_user_report_pdf', compact('results', 'month', 'userId', 'startDate', 'endDate', 'selectedOutsideFy', 'selectedFyId'));
        return $pdf->download('monthly_user_report.pdf');
    }

    /**
     * AJAX: Return user's call logs for the month for modal popup
     */
    public function userCallsModal(Request $request, $userId)
    {
        $month = $request->input('month', date('Y-m'));
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        $calls = \App\Models\CallLog::where('created_by', $userId)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->get(['name', 'company_name', 'mobile_number', 'call_status']);
        if ($calls->isEmpty()) {
            return response()->json(['html' => '<p>No data found.</p>']);
        }
        $html = '<table class="table table-bordered"><thead><tr><th>Name</th><th>Company</th><th>Mobile</th><th>Status</th></tr></thead><tbody>';
        foreach ($calls as $call) {
            $html .= '<tr>';
            $html .= '<td>' . e($call->name) . '</td>';
            $html .= '<td>' . e($call->company_name) . '</td>';
            $html .= '<td>' . e($call->mobile_number) . '</td>';
            $html .= '<td>' . e($call->call_status) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * User Daily Report: Returns daily report for users with required fields and date range
     */
     public function userDailyReport(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('user_report_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $currentUser = auth()->user();
        $isAdminOrManager = in_array($currentUser->crm_role_type, [0, 1, 2]);

        // If regular employee, only show their own report
        $userId = $request->input('user_id');
        if (!$isAdminOrManager && !$userId) {
            $userId = $currentUser->id;
        } elseif (!$isAdminOrManager && $userId && $userId != $currentUser->id) {
            // Regular employee trying to view another employee's report
            abort(403, 'You can only view your own report.');
        }

        // Do not default start/end immediately; apply financial year semantics first
        $startDate = $request->input('start_date', null);
        $endDate = $request->input('end_date', null);

        // Resolve selected or active financial year
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }

        $fyStart = null;
        $fyEnd = null;
        if (!empty($fy)) {
            try {
                $fyStart = \Carbon\Carbon::parse($fy->from_date)->startOfDay();
                $fyEnd = \Carbon\Carbon::parse($fy->to_date)->endOfDay();
            } catch (\Exception $e) {
                $fyStart = null;
                $fyEnd = null;
            }
        }

        $selectedOutsideFy = false;

        // If no explicit dates provided, default to current date (today)
        if (empty($startDate) && empty($endDate)) {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        } else {
            // Normalize partial inputs: if one side missing, use the other side
            if (empty($startDate) && !empty($endDate)) {
                $startDate = $endDate;
            }
            if (empty($endDate) && !empty($startDate)) {
                $endDate = $startDate;
            }

            // If FY exists, intersect the requested range with FY range
            if (!empty($fyStart) && !empty($fyEnd)) {
                try {
                    $reqStart = \Carbon\Carbon::parse($startDate)->startOfDay();
                    $reqEnd = \Carbon\Carbon::parse($endDate)->endOfDay();
                    $interStart = $reqStart->gt($fyStart) ? $reqStart : $fyStart;
                    $interEnd = $reqEnd->lt($fyEnd) ? $reqEnd : $fyEnd;
                    if ($interStart->gt($interEnd)) {
                        // No overlap with FY -> mark outside and return empty
                        $selectedOutsideFy = true;
                    } else {
                        $startDate = $interStart->format('Y-m-d');
                        $endDate = $interEnd->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    // On parse error fallback to FY or today
                    if (!empty($fyStart) && !empty($fyEnd)) {
                        $startDate = $fyStart->format('Y-m-d');
                        $endDate = $fyEnd->format('Y-m-d');
                    } else {
                        $startDate = date('Y-m-d');
                        $endDate = date('Y-m-d');
                    }
                }
            }
        }
        $usersQuery = \App\Models\User::query();
        if($currentUser->crm_role_type === 2) {
            // If manager, only show their team members
            $usersQuery->where('assign_manager', $currentUser->id)->orWhere('id', $currentUser->id);
        } else {
             if ($userId) {
                $usersQuery->where('id', $userId);
            }
        }

        $users = $usersQuery->get();
        $results = [];
        // If the requested range is outside the selected FY, return empty results
        if (!$selectedOutsideFy) {
            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
            foreach ($users as $user) {
                foreach ($period as $dateObj) {
                    $date = $dateObj->format('Y-m-d');
                    $call_count = \App\Models\CallLog::where('created_by', $user->id)
                        ->whereDate('created_at', $date)
                        ->count();
                    $leads = \App\Models\Lead::where('user_owner_id', $user->id)
                        ->whereDate('created_at', $date)
                        ->get();
                    $deals = \App\Models\Deal::where('user_owner_id', $user->id)
                        ->whereDate('created_at', $date)
                        ->get();
                    if ($leads->isEmpty() && $deals->isEmpty()) {
                        $results[] = [
                            'user' => $user,
                            'date' => date('d-m-Y', strtotime($date)),
                            'call_count' => $call_count,
                            'lead_name' => '',
                            'lead_source' => '',
                            'deal_title' => '',
                            'stage' => '',
                            'status' => '',
                            'closed_date' => '',
                            'deal_amount' => '',
                            'loss_reason' => '',
                        ];
                    } else {
                        // Map deals to leads by converted_lead_id
                        foreach ($leads as $lead) {
                            $deal = $deals->where('converted_lead_id', $lead->id)->first();
                            $lead_source = '';
                            if ($lead->lead_source) {
                                $sourceObj = \App\Models\LeadSource::where('id', $lead->lead_source)->orWhere('name', $lead->lead_source)->first();
                                $lead_source = $sourceObj ? $sourceObj->name : $lead->lead_source;
                            }
                            $stage = $deal ? $deal->stage : '';
                            $status = $deal ? $deal->status : '';
                            $closed_date = $deal ? ($deal->close_date ? date('d-m-Y', strtotime($deal->close_date)) : '') : '';
                            $deal_amount = $deal ? (is_numeric($deal->amount) ? (float)$deal->amount : $deal->amount) : '';
                            $loss_reason = ($deal && strtolower($deal->stage) === 'closed lost') ? $deal->reason_for_loss : '';
                            $deal_title = $deal ? $deal->title : '';
                            $results[] = [
                                'user' => $user,
                                'date' => date('d-m-Y', strtotime($date)),
                                'call_count' => $call_count,
                                'lead_name' => $lead->title,
                                'lead_source' => $lead_source,
                                'deal_title' => $deal_title,
                                'stage' => $stage,
                                'status' => $status,
                                'closed_date' => $closed_date,
                                'deal_amount' => $deal_amount,
                                'loss_reason' => $loss_reason,
                            ];
                        }
                         // If there are deals but no leads, add a leading row to show call_count
                         if ($leads->isEmpty() && $deals->isNotEmpty()) {
                             $results[] = [
                                 'user' => $user,
                                 'date' => date('d-m-Y', strtotime($date)),
                                 'call_count' => $call_count,
                                 'lead_name' => '',
                                 'lead_source' => '',
                                 'deal_title' => '',
                                 'stage' => '',
                                 'status' => '',
                                 'closed_date' => '',
                                 'deal_amount' => '',
                                 'loss_reason' => '',
                             ];
                         }

                         // Add deals not linked to a lead
                         foreach ($deals as $deal) {
                             if (!$leads->where('id', $deal->converted_lead_id)->count()) {
                                 $results[] = [
                                     'user' => $user,
                                     'date' => date('d-m-Y', strtotime($date)),
                                     'call_count' => '',
                                     'lead_name' => '',
                                     'lead_source' => '',
                                     'deal_title' => $deal->title,
                                     'stage' => $deal->stage,
                                     'status' => $deal->status,
                                     'closed_date' => $deal->close_date ? date('d-m-Y', strtotime($deal->close_date)) : '',
                                     'deal_amount' => is_numeric($deal->amount) ? (float)$deal->amount : $deal->amount,
                                     'loss_reason' => strtolower($deal->stage) === 'closed lost' ? $deal->reason_for_loss : '',
                                 ];
                             }
                         }
                    }
                }
            }
        }
        return view('reports.user_daily_report', compact('results', 'startDate', 'endDate', 'userId', 'selectedOutsideFy'));
    }

    /**
     * Export Daily User Report as Excel
     */
    public function exportDailyUserReportExcel(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('user_report_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $currentUser = auth()->user();
        $isAdminOrManager = in_array($currentUser->crm_role_type, [0, 1, 2]);

        // If regular employee, only show their own report
        $userId = $request->input('user_id');
        if (!$isAdminOrManager && !$userId) {
            $userId = $currentUser->id;
        } elseif (!$isAdminOrManager && $userId && $userId != $currentUser->id) {
            // Regular employee trying to view another employee's report
            abort(403, 'You can only export your own report.');
        }

        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $usersQuery = \App\Models\User::query();
        if($currentUser->crm_role_type === 2) {
            // If manager, only show their team members
            $usersQuery->where('assign_manager', $currentUser->id)->orWhere('id', $currentUser->id);
        } else {
             if ($userId) {
                $usersQuery->where('id', $userId);
            }
        }
        $users = $usersQuery->get();
        $results = [];
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($users as $user) {
            foreach ($period as $dateObj) {
                $date = $dateObj->format('Y-m-d');
                $call_count = \App\Models\CallLog::where('created_by', $user->id)
                    ->whereDate('created_at', $date)
                    ->count();
                $leads = \App\Models\Lead::where('user_owner_id', $user->id)
                    ->whereDate('created_at', $date)
                    ->get();
                $deals = \App\Models\Deal::where('user_owner_id', $user->id)
                    ->whereDate('created_at', $date)
                    ->get();
                if ($leads->isEmpty() && $deals->isEmpty()) {
                    $results[] = [
                        'user' => $user,
                        'date' => date('d-m-Y', strtotime($date)),
                        'call_count' => $call_count,
                        'lead_name' => '',
                        'lead_source' => '',
                        'deal_title' => '',
                        'stage' => '',
                        'status' => '',
                        'closed_date' => '',
                        'deal_amount' => '',
                        'loss_reason' => '',
                    ];
                } else {
                    foreach ($leads as $lead) {
                        $deal = $deals->where('converted_lead_id', $lead->id)->first();
                        $lead_source = '';
                        if ($lead->lead_source) {
                            $sourceObj = \App\Models\LeadSource::where('id', $lead->lead_source)->orWhere('name', $lead->lead_source)->first();
                            $lead_source = $sourceObj ? $sourceObj->name : $lead->lead_source;
                        }
                        $stage = $deal ? $deal->stage : '';
                        $status = $deal ? $deal->status : '';
                        $closed_date = $deal ? ($deal->close_date ? date('d-m-Y', strtotime($deal->close_date)) : '') : '';
                        $deal_amount = $deal ? (is_numeric($deal->amount) ? (float)$deal->amount : $deal->amount) : '';
                        $loss_reason = ($deal && strtolower($deal->stage) === 'closed lost') ? $deal->reason_for_loss : '';
                        $deal_title = $deal ? $deal->title : '';
                        $results[] = [
                            'user' => $user,
                            'date' => date('d-m-Y', strtotime($date)),
                            'call_count' => $results ? '' : $call_count,
                            'lead_name' => $lead->title,
                            'lead_source' => $lead_source,
                            'deal_title' => $deal_title,
                            'stage' => $stage,
                            'status' => $status,
                            'closed_date' => $closed_date,
                            'deal_amount' => $deal_amount,
                            'loss_reason' => $loss_reason,
                        ];
                    }
                    foreach ($deals as $deal) {
                        if (!$leads->where('id', $deal->converted_lead_id)->count()) {
                            $results[] = [
                                'user' => $user,
                                'date' => date('d-m-Y', strtotime($date)),
                                'call_count' => '',
                                'lead_name' => '',
                                'lead_source' => '',
                                'deal_title' => $deal->title,
                                'stage' => $deal->stage,
                                'status' => $deal->status,
                                'closed_date' => $deal->close_date ? date('d-m-Y', strtotime($deal->close_date)) : '',
                                'deal_amount' => is_numeric($deal->amount) ? (float)$deal->amount : $deal->amount,
                                'loss_reason' => strtolower($deal->stage) === 'closed lost' ? $deal->reason_for_loss : '',
                            ];
                        }
                    }
                }
            }
        }
        $fileName = 'user_daily_report_' . $startDate . '_to_' . $endDate . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\DailyUserReportExport($results),
            $fileName
        );
    }

     /**
     * Show monthly user performance report: sales target, achieved sales, progress.
     * Supports optional year/month filter via request.
     * Fetch currency details from Company table.
     */
    public function monthlyUserPerformanceReport(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('view_crm_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $currentUser = auth()->user();
        $isAdminOrManager = in_array($currentUser->crm_role_type, [0, 1, 2]);

        // If regular employee, only show their own report
        $user_id = $request->input('user_id');
        if (!$isAdminOrManager && !$user_id) {
            $user_id = $currentUser->id;
        } elseif (!$isAdminOrManager && $user_id && $user_id != $currentUser->id) {
            // Regular employee trying to view another employee's report
            abort(403, 'You can only view your own report.');
        }

        // Do not default year/month here so we can detect whether they were explicitly provided
        $year = $request->input('year', null);
        $month = $request->input('month', null);
        // Financial year selection: if a financial year is selected and no explicit year/month
        // is provided, use the FY range to limit which monthly records are shown.
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }
        $query = \App\Models\UserMonthlySales::with('user')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        // Determine FY range if available
        $fyStart = null;
        $fyEnd = null;
        if (!empty($fy)) {
            try {
                $fyStart = \Carbon\Carbon::parse($fy->from_date)->startOfMonth();
                $fyEnd = \Carbon\Carbon::parse($fy->to_date)->endOfMonth();
            } catch (\Exception $e) {
                $fyStart = null;
                $fyEnd = null;
            }
        }

        // Build requested months list (intersected with FY if FY is present)
        $months = [];

        // Helper: check if a year/month is inside FY range
        $insideFy = function($y, $m) use ($fyStart, $fyEnd) {
            if (empty($fyStart) || empty($fyEnd)) return true; // no FY to check against
            try {
                $d = \Carbon\Carbon::create($y, $m, 1)->startOfMonth();
            } catch (\Exception $e) {
                return false;
            }
            return $d->gte($fyStart->copy()->startOfMonth()) && $d->lte($fyEnd->copy()->startOfMonth());
        };

        // If explicit year and month provided -> validate against FY and use that single month
        if ($request->filled('year') || $request->filled('month')) {
            // when one of them is missing, we interpret accordingly (see below)
            $reqYear = $request->filled('year') ? (int)$year : null;
            $reqMonth = $request->filled('month') ? (int)$month : null;

            if ($reqYear && $reqMonth) {
                // both provided: only include if inside FY (if FY exists)
                if ($insideFy($reqYear, $reqMonth)) {
                    $months[] = ['year' => $reqYear, 'month' => $reqMonth];
                } else {
                    // explicit month/year outside FY -> no data
                    $months = [];
                }
            } elseif ($reqYear && !$reqMonth) {
                // year provided: include all months within FY that match this year (if FY),
                // otherwise include all months 1..12 for that year
                if (!empty($fyStart) && !empty($fyEnd)) {
                    $p = $fyStart->copy();
                    while ($p->lte($fyEnd)) {
                        if ((int)$p->format('Y') === $reqYear) {
                            $months[] = ['year' => (int)$p->format('Y'), 'month' => (int)$p->format('n')];
                        }
                        $p->addMonth();
                    }
                } else {
                    for ($m = 1; $m <= 12; $m++) {
                        $months[] = ['year' => $reqYear, 'month' => $m];
                    }
                }
            } elseif ($reqMonth && !$reqYear) {
                // month provided but no year: include all FY months that match this month,
                // or if no FY, include current year-only month
                if (!empty($fyStart) && !empty($fyEnd)) {
                    $p = $fyStart->copy();
                    while ($p->lte($fyEnd)) {
                        if ((int)$p->format('n') === $reqMonth) {
                            $months[] = ['year' => (int)$p->format('Y'), 'month' => (int)$p->format('n')];
                        }
                        $p->addMonth();
                    }
                } else {
                    $months[] = ['year' => (int)date('Y'), 'month' => $reqMonth];
                }
            }
        } else {
            // No explicit year/month: show current month only
            $months[] = ['year' => (int)date('Y'), 'month' => (int)date('n')];
        }

        // If months list is empty, we will return no data (respecting FY constraints)
        $report = [];

        if (!empty($months)) {
            // Fetch monthly sales rows for these months and optional user filter
            $queryMonths = \App\Models\UserMonthlySales::with('user')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc');

            if ($currentUser->crm_role_type === 2) {
                // If the user is a manager, limit to their team members
                $teamMemberIds = User::where('assign_manager', $currentUser->id)->pluck('id')->toArray();
                $queryMonths->whereIn('user_id', $teamMemberIds)
                    ->orWhere('user_id', $currentUser->id);
            }

            if ($user_id) {
                $queryMonths->where('user_id', $user_id);
            }

            // Add where clauses for each year/month pair
            $queryMonths->where(function ($q) use ($months) {
                foreach ($months as $m) {
                    $q->orWhere(function ($q2) use ($m) {
                        $q2->where('year', $m['year'])->where('month', $m['month']);
                    });
                }
            });

            $monthlySales = $queryMonths->get();

            // Map existing rows
            $map = [];
            foreach ($monthlySales as $row) {
                $map[$row->user_id . '|' . $row->year . '|' . $row->month] = $row;
            }

            // Get users list for output (respect crm_role_type)
            if ($currentUser->crm_role_type === 2) {
                // If the user is a manager, limit to their team members
                $allUsers = User::where('assign_manager', $currentUser->id)
                    ->orWhere('id', $currentUser->id)
                    ->orderBy('name')
                    ->get();
            } else {
                $allUsers = User::where('crm_role_type', '!=', 0)->orderBy('name')->get();
            }

            foreach ($allUsers as $u) {
                if ($user_id && $user_id != $u->id) continue;
                foreach ($months as $m) {
                    $key = $u->id . '|' . $m['year'] . '|' . $m['month'];
                    if (isset($map[$key])) {
                        $row = $map[$key];
                        $report[] = [
                            'user_id' => $row->user_id,
                            'user_name' => $row->user ? $row->user->name : $u->name,
                            'year' => $row->year,
                            'month' => $row->month,
                            'sales_target' => $row->sales_target,
                            'achieved_sales' => $row->achieved_sales,
                            'generated_deals' => $row->generated_deals ?? 0, // Added field
                            'contacted_leads' => $row->contacted_leads ?? 0, // Added field
                            'progress' => $row->sales_target > 0 ? round(($row->achieved_sales / $row->sales_target) * 100, 2) : 0
                        ];
                    } else {
                        $report[] = [
                            'user_id' => $u->id,
                            'user_name' => $u->name,
                            'year' => $m['year'],
                            'month' => $m['month'],
                            'sales_target' => 0,
                            'achieved_sales' => 0,
                            'generated_deals' => 0, // Default value
                            'contacted_leads' => 0, // Default value
                            'progress' => 0
                        ];
                    }
                }
            }
        }
        $company = \App\Models\Company::first();
        $currency_symbol = $company ? $company->currency_symbol : config('app.currency_symbol', '₹');
        // Only show users who are not superadmin (assuming crm_role_type 0 = superadmin)
        $users = User::where('crm_role_type', '!=', 0)->orderBy('name')->get();
        return view('reports.monthly_user_performance', compact('report', 'year', 'month', 'currency_symbol', 'users', 'user_id'));
    }

    public function displayAnalyticsReports(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('view_crm_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $currentUser = auth()->user();
        $isAdminOrManager = in_array($currentUser->crm_role_type, [0, 1, 2]);

        // If regular employee, only show their own report
        $user_id = $request->input('user_id');
        if (!$isAdminOrManager && !$user_id) {
            $user_id = $currentUser->id;
        } elseif (!$isAdminOrManager && $user_id && $user_id != $currentUser->id) {
            // Regular employee trying to view another employee's report
            abort(403, 'You can only view your own report.');
        }

        // Do not default year/month here so we can detect whether they were explicitly provided
        $year = $request->input('year', null);
        $month = $request->input('month', null);
        // Financial year selection: if a financial year is selected and no explicit year/month
        // is provided, use the FY range to limit which monthly records are shown.
        $selectedFyId = session('selected_financial_year', null);
        $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
        } else {
            $fy = $activeFy;
        }
        $query = \App\Models\UserMonthlySales::with('user')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        // Determine FY range if available
        $fyStart = null;
        $fyEnd = null;
        if (!empty($fy)) {
            try {
                $fyStart = \Carbon\Carbon::parse($fy->from_date)->startOfMonth();
                $fyEnd = \Carbon\Carbon::parse($fy->to_date)->endOfMonth();
            } catch (\Exception $e) {
                $fyStart = null;
                $fyEnd = null;
            }
        }

        // Build requested months list (intersected with FY if FY is present)
        $months = [];

        // Helper: check if a year/month is inside FY range
        $insideFy = function($y, $m) use ($fyStart, $fyEnd) {
            if (empty($fyStart) || empty($fyEnd)) return true; // no FY to check against
            try {
                $d = \Carbon\Carbon::create($y, $m, 1)->startOfMonth();
            } catch (\Exception $e) {
                return false;
            }
            return $d->gte($fyStart->copy()->startOfMonth()) && $d->lte($fyEnd->copy()->startOfMonth());
        };

        // If explicit year and month provided -> validate against FY and use that single month
        if ($request->filled('year') || $request->filled('month')) {
            // when one of them is missing, we interpret accordingly (see below)
            $reqYear = $request->filled('year') ? (int)$year : null;
            $reqMonth = $request->filled('month') ? (int)$month : null;

            if ($reqYear && $reqMonth) {
                // both provided: only include if inside FY (if FY exists)
                if ($insideFy($reqYear, $reqMonth)) {
                    $months[] = ['year' => $reqYear, 'month' => $reqMonth];
                } else {
                    // explicit month/year outside FY -> no data
                    $months = [];
                }
            } elseif ($reqYear && !$reqMonth) {
                // year provided: include all months within FY that match this year (if FY),
                // otherwise include all months 1..12 for that year
                if (!empty($fyStart) && !empty($fyEnd)) {
                    $p = $fyStart->copy();
                    while ($p->lte($fyEnd)) {
                        if ((int)$p->format('Y') === $reqYear) {
                            $months[] = ['year' => (int)$p->format('Y'), 'month' => (int)$p->format('n')];
                        }
                        $p->addMonth();
                    }
                } else {
                    for ($m = 1; $m <= 12; $m++) {
                        $months[] = ['year' => $reqYear, 'month' => $m];
                    }
                }
            } elseif ($reqMonth && !$reqYear) {
                // month provided but no year: include all FY months that match this month,
                // or if no FY, include current year-only month
                if (!empty($fyStart) && !empty($fyEnd)) {
                    $p = $fyStart->copy();
                    while ($p->lte($fyEnd)) {
                        if ((int)$p->format('n') === $reqMonth) {
                            $months[] = ['year' => (int)$p->format('Y'), 'month' => (int)$p->format('n')];
                        }
                        $p->addMonth();
                    }
                } else {
                    $months[] = ['year' => (int)date('Y'), 'month' => $reqMonth];
                }
            }
        } else {
            // No explicit year/month: show current month only
            $months[] = ['year' => (int)date('Y'), 'month' => (int)date('n')];
        }

        // If months list is empty, we will return no data (respecting FY constraints)
        $report = [];

        if (!empty($months)) {
            // Fetch monthly sales rows for these months and optional user filter
            $queryMonths = \App\Models\UserMonthlySales::with('user')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc');

            if ($currentUser->crm_role_type === 2) {
                // If the user is a manager, limit to their team members
                $teamMemberIds = User::where('assign_manager', $currentUser->id)->pluck('id')->toArray();
                $queryMonths->whereIn('user_id', $teamMemberIds)
                    ->orWhere('user_id', $currentUser->id);
            }

            if ($user_id) {
                $queryMonths->where('user_id', $user_id);
            }

            // Add where clauses for each year/month pair
            $queryMonths->where(function ($q) use ($months) {
                foreach ($months as $m) {
                    $q->orWhere(function ($q2) use ($m) {
                        $q2->where('year', $m['year'])->where('month', $m['month']);
                    });
                }
            });

            $monthlySales = $queryMonths->get();

            // Map existing rows
            $map = [];
            foreach ($monthlySales as $row) {
                $map[$row->user_id . '|' . $row->year . '|' . $row->month] = $row;
            }

            // Get users list for output (respect crm_role_type)
            if ($currentUser->crm_role_type === 2) {
                // If the user is a manager, limit to their team members
                $allUsers = User::where('assign_manager', $currentUser->id)
                    ->orWhere('id', $currentUser->id)
                    ->orderBy('name')
                    ->get();
            } else {
                $allUsers = User::where('crm_role_type', '!=', 0)->orderBy('name')->get();
            }

            foreach ($allUsers as $u) {
                if ($user_id && $user_id != $u->id) continue;
                foreach ($months as $m) {
                    $key = $u->id . '|' . $m['year'] . '|' . $m['month'];
                    $startDate = \Carbon\Carbon::create($m['year'], $m['month'], 1)->startOfMonth();
                    $endDate = \Carbon\Carbon::create($m['year'], $m['month'], 1)->endOfMonth();

                    $generatedDealsCount = \App\Models\Deal::where('user_owner_id', $u->id)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->count();

                    $contactedLeadsCount = \App\Models\Lead::where('user_owner_id', $u->id)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->count();

                    if (isset($map[$key])) {
                        $row = $map[$key];
                        $report[] = [
                            'user_id' => $row->user_id,
                            'user_name' => $row->user ? $row->user->name : $u->name,
                            'year' => $row->year,
                            'month' => $row->month,
                            'sales_target' => $row->sales_target,
                            'achieved_sales' => $row->achieved_sales,
                            'generated_deals' => $generatedDealsCount,
                            'contacted_leads' => $contactedLeadsCount,
                            'progress' => $row->sales_target > 0 ? round(($row->achieved_sales / $row->sales_target) * 100, 2) : 0
                        ];
                    } else {
                        $report[] = [
                            'user_id' => $u->id,
                            'user_name' => $u->name,
                            'year' => $m['year'],
                            'month' => $m['month'],
                            'sales_target' => 0,
                            'achieved_sales' => 0,
                            'generated_deals' => 0,
                            'contacted_leads' => 0,
                            'progress' => 0
                        ];
                    }
                }
            }
        }
        $company = \App\Models\Company::first();
        $currency_symbol = $company ? $company->currency_symbol : config('app.currency_symbol', '₹');
        // Only show users who are not superadmin (assuming crm_role_type 0 = superadmin)
        $users = User::where('crm_role_type', '!=', 0)->orderBy('name')->get();
        return view('reports.analytics_report', compact('report', 'year', 'month', 'currency_symbol', 'users', 'user_id'));
    }

    public function productCategoryUserReport()
    {
        if (!auth()->user()->hasCrmPermission('user_report_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $userId = request('user_id');
        $categoryId = request('category');
        $fromDate = request('start_date');
        $toDate = request('end_date');

        // Filter users
        $usersQuery = User::query();
        if ($userId) {
            $usersQuery->where('id', $userId);
        }
        $usersQuery->where('crm_role_type', '!=', '0');
        $users = $usersQuery->get();

        // Filter leads
        $leadsQuery = Lead::query();
        if ($userId) {
            $leadsQuery->where('user_owner_id', $userId);
        }
        if ($categoryId && $categoryId !== 'all') {
            $leadsQuery->whereRaw("FIND_IN_SET(?, category)", [$categoryId]);
        }
        if ($fromDate) {
            $leadsQuery->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $leadsQuery->whereDate('created_at', '<=', $toDate);
        }
        $leads = $leadsQuery->get();

        // Filter deals
        $dealsQuery = Deal::query();
        if ($userId) {
            $dealsQuery->where('user_owner_id', $userId);
        }
        if ($categoryId && $categoryId !== 'all') {
            $dealsQuery->whereRaw("FIND_IN_SET(?, category)", [$categoryId]);
        }
        if ($fromDate) {
            $dealsQuery->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $dealsQuery->whereDate('created_at', '<=', $toDate);
        }
        $deals = $dealsQuery->get();

        // Prepare report data grouped by category
        $results = [];
        foreach ($users as $user) {
            $userLeads = $leads->where('user_owner_id', $user->id);
            $userDeals = $deals->where('user_owner_id', $user->id);

            $allCategories = \App\Models\ProductCategory::pluck('category_name', 'id');

            foreach ($allCategories as $catId => $catName) {
                if ($categoryId && $categoryId !== 'all' && $catId != $categoryId) {
                    continue; // Skip other categories if a specific one is selected
                }

                $leadCount = $userLeads->filter(function ($lead) use ($catId) {
                    return in_array($catId, explode(',', $lead->category));
                })->count();

                $dealCount = $userDeals->filter(function ($deal) use ($catId) {
                    return in_array($catId, explode(',', $deal->category));
                })->count();
                $dealWon = $userDeals->filter(function ($deal) use ($catId) {
                    return in_array($catId, explode(',', $deal->category)) 
                        && $deal->stage == 'Closed Won';
                })->count();
                $dealLost = $userDeals->filter(function ($deal) use ($catId) {
                    return in_array($catId, explode(',', $deal->category)) 
                        && $deal->stage == 'Closed Lost';
                })->count();

                $results[$catName][] = [
                    'user_id' => $user->id,
                    'user' => $user->name,
                    'lead_count' => $leadCount,
                    'deal_count' => $dealCount,
                    'deal_won' => $dealWon,
                    'deal_lost' => $dealLost,
                    'category_id' => $catId
                ];
            }

            // Add "No Category" for leads and deals without a category
            if (!$categoryId || $categoryId === 'all') {
                $noCategoryLeadCount = $userLeads->filter(function ($lead) {
                    return empty($lead->category);
                })->count();

                $noCategoryDealCount = $userDeals->filter(function ($deal) {
                    return empty($deal->category);
                })->count();

                $noCategoryDealWon = $userDeals->filter(function ($deal) {
                    return empty($deal->category) 
                        && strtolower($deal->stage) === 'closed won';
                })->count();
                $noCategoryDealLost = $userDeals->filter(function ($deal) {
                    return empty($deal->category) 
                        && strtolower($deal->stage) === 'closed lost';
                })->count();

                $results['No Category'][] = [
                    'user_id' => $user->id,
                    'user' => $user->name,
                    'lead_count' => $noCategoryLeadCount,
                    'deal_count' => $noCategoryDealCount,
                    'deal_won' => $noCategoryDealWon,
                    'deal_lost' => $noCategoryDealLost,
                    'category_id' => null
                ];
            }
        }

        return view('reports.product_category_user_report', compact('results'));
    }

    /**
     * Fetch leads list for a user and category.
     */
    public function fetchLeadsList($userId)
    {
        if (!auth()->user()->hasCrmPermission('user_report_guard')) {
            abort(403, 'Unauthorized action.');
        }
        
        $category = request()->query('category');

        $leadsQuery = Lead::query()->where('user_owner_id', $userId);

        if ($category) {
            $leadsQuery->whereRaw("FIND_IN_SET(?, category)", [$category]);
        } else {
            $leadsQuery->whereNull('category');
        }

        $leads = $leadsQuery->get();

        return view('partials.leads_list', compact('leads'));
    }

    /**
     * Fetch deals list for a user and category.
     */
    public function fetchDealsList($userId)
    {
         if (!auth()->user()->hasCrmPermission('user_report_guard')) {
            abort(403, 'Unauthorized action.');
        }
        
        $category = request()->query('category');
        $stage = request()->query('stage'); // Optional stage filter for deals list 

        $dealsQuery = Deal::query()->where('user_owner_id', $userId);

        if ($category) {
            $dealsQuery->whereRaw("FIND_IN_SET(?, category)", [$category]);
        } else {
            $dealsQuery->whereNull('category');
        }

        if ($stage) {
            $dealsQuery->where('stage', $stage);
        }

        $deals = $dealsQuery->get();

        return view('partials.deals_list', compact('deals'));
    }
}
