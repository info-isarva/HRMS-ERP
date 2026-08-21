<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Person as Contact;

class DealController extends Controller
{
    public function __construct()
    {
        // Apply middleware to block create/edit/delete actions when a historical financial year is selected.
        // Use the middleware class directly to avoid alias resolution issues.
        $this->middleware(\App\Http\Middleware\PreventHistoricalFinancialYear::class)->only([
            'create', 'store', 'createFromLead', 'storeFromLead',
            'edit', 'update', 'destroy', 'markWon', 'markLost', 'reopen', 'updateStage'
        ]);
    }

    public function index()
    {
        // Deals Pipeline View

        if (!auth()->user()->hasCrmPermission('manage_crm_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $user = auth()->user();
        $stages = \App\Models\Stage::orderBy('order_by')->get();
        $query = \App\Models\Deal::with(['organization', 'person', 'owner']);
        // Role-based base filter
        if (!($user->crm_role_type === 0 || $user->crm_role_type === 1 || $user->crm_role_type === 2)) {
            $query->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('user_owner_id', $user->id);
            });
        }elseif ($user->crm_role_type === 2) { // Manager
            $employeeIds = User::where('assign_manager', $user->id)->pluck('id');
            $query->where(function($q) use ($user, $employeeIds) {
                $q->where('created_by', $user->id)
                  ->orWhere('user_owner_id', $user->id)
                  ->orWhereIn('created_by', $employeeIds)
                  ->orWhereIn('user_owner_id', $employeeIds);
            });
        }

        // Search/Filter logic (allow pipeline to accept same filters)
        $title = request('title');
        $label = request('label');
        $view = request('view');
        $lead_source = request('lead_source');
        $reqStart = request('start_date');
        $reqEnd = request('end_date');
        $category = request('category');

        if ($title) {
            $query->where('title', 'like', "%$title%");
        }
        if ($label) {
            $query->where('label', $label);
        }
        // Apply 'view' filters (Closed Won / Closed Lost / My Deals / Open Deals / Recently ...)
        if ($view) {
            switch ($view) {
                case 'Closed Won':
                    $query->where('status', 'closed won');
                    break;
                case 'Closed Lost':
                    $query->where('status', 'closed lost');
                    break;
                case 'Open Deals':
                    $query->where('status', 'open');
                    break;
                case 'My Deals':
                    $query->where('user_owner_id', $user->id);
                    break;
                case 'Recently Created Deals':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'Recently Modified Deals':
                    $query->orderBy('updated_at', 'desc');
                    break;
                case 'All Deals':
                default:
                    // no-op
                    break;
            }
        }
        if ($lead_source) {
            $query->where('lead_source', $lead_source);
        }

        // Apply date filters (start_date / end_date) if provided. Respect selected financial year
        $selectedFyId = session('selected_financial_year', null);
        $fyFrom = null; $fyTo = null;
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
            if ($fy) {
                $fyFrom = \Carbon\Carbon::parse($fy->from_date)->startOfDay();
                $fyTo = \Carbon\Carbon::parse($fy->to_date)->endOfDay();
            }
        }

        try { $start = $reqStart ? \Carbon\Carbon::parse($reqStart)->startOfDay() : null; } catch (\Exception $e) { $start = null; }
        try { $end = $reqEnd ? \Carbon\Carbon::parse($reqEnd)->endOfDay() : null; } catch (\Exception $e) { $end = null; }

        if ($start || $end) {
            if ($fyFrom) { if ($start && $start->lt($fyFrom)) $start = $fyFrom; if (!$start) $start = $fyFrom; }
            if ($fyTo) { if ($end && $end->gt($fyTo)) $end = $fyTo; if (!$end) $end = $fyTo; }
            if ($start && $end && $start->gt($end)) {
                $query->whereRaw('0 = 1');
            } else {
                if ($start && $end) { $query->whereBetween('created_at', [$start, $end]); }
                elseif ($start) { $query->where('created_at', '>=', $start); }
                elseif ($end) { $query->where('created_at', '<=', $end); }
            }
        } else {
            if ($fyFrom && $fyTo) { $query->whereBetween('created_at', [$fyFrom, $fyTo]); }
        }

        if ($category) {
            \Log::info('Category Filter Debug:', [
                'category_input' => $category,
                'query_before' => $query->toSql(),
            ]);

            $query->whereRaw("FIND_IN_SET(?, category)", [$category]);

            \Log::info('Query After Applying Filter:', ['query_after' => $query->toSql()]);
        }

        $deals = $query->orderByDesc('created_at')->get();

        // Optionally, add computed fields for display
        foreach ($deals as $deal) {
            $deal->stage_name = $deal->stage;
            $deal->contact_name = $deal->person ? ($deal->person->first_name . ' ' . $deal->person->last_name) : ($deal->person_name ?? null);
            $deal->owner_name = $deal->owner ? $deal->owner->name : null;
        }

        $leadSources = \App\Models\LeadSource::orderBy('name')->get();
        $priorities = [ ['value'=>'high','label'=>'High'], ['value'=>'normal','label'=>'Normal'], ['value'=>'low','label'=>'Low'] ];
         // Fetch active categories for the filter
        $categories = \App\Models\ProductCategory::where('status', 1)->orderBy('category_name')->get();
        if (!$categories || $categories->isEmpty()) {
            $categories = collect(['No Categories Available']); // Default fallback
        }
        return view('deals.pipeline', compact('stages', 'deals', 'leadSources', 'priorities', 'user', 'categories'));
    }

    public function list()
    {
        if (!auth()->user()->hasCrmPermission('manage_crm_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $user = auth()->user();
        $view = request('view', 'All Deals');
        $createdThisMonth = request('created_this_month');
        $closingThisMonth = request('closing_this_month');
        $query = \App\Models\Deal::with(['owner', 'stage']);

        // Role-based base filter
        if (!($user->crm_role_type === 0 || $user->crm_role_type === 1 || $user->crm_role_type === 2)) {
            $query->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('user_owner_id', $user->id);
            });
        }elseif ($user->crm_role_type === 2) { // Manager
            $employeeIds = User::where('assign_manager', $user->id)->pluck('id');
            $query->where(function($q) use ($user, $employeeIds) {
                $q->where('created_by', $user->id)
                  ->orWhere('user_owner_id', $user->id)
                  ->orWhereIn('created_by', $employeeIds)
                  ->orWhereIn('user_owner_id', $employeeIds);
            });
        }

        // View filter
        switch ($view) {
            case 'Closed Won':
                $query->where('status', 'closed won');
                break;
            case 'Closed Lost':
                $query->where('status', 'closed lost');
                break;
            case 'Open Deals':
                $query->where('status', 'open');
                break;
            case 'My Deals':
                $query->where('user_owner_id', $user->id);
                break;
            case 'Recently Created Deals':
                $query->orderBy('created_at', 'desc');
                break;
            case 'Recently Modified Deals':
                $query->orderBy('updated_at', 'desc');
                break;
            case 'All Deals':
            default:
                // No additional filter
                break;
        }

        // Search/Filter logic
        $title = request('title');
        $label = request('label');
        $stage = request('stage');
        $lead_source = request('lead_source');
        $reqStart = request('start_date');
        $reqEnd = request('end_date');

        if ($title) {
            $query->where('title', 'like', "%$title%");
        }
        if ($label) {
            $query->where('label', $label);
        }
        if ($stage) {
            $query->where('stage', $stage);
        }
        if ($lead_source) {
            $query->where('lead_source', $lead_source);
        }

        // Filter for deals created this month (from dashboard card)
        if ($createdThisMonth) {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        }

        // Filter for deals closing this month (from dashboard card)
        if ($closingThisMonth) {
            $query->whereMonth('close_date', now()->month)->whereYear('close_date', now()->year);
        }

        // For filter dropdowns
        $stages = \App\Models\Stage::orderBy('order_by')->get();
        $leadSources = \App\Models\LeadSource::orderBy('name')->get();
        $priorities = [
            ['value' => 'high', 'label' => 'High'],
            ['value' => 'normal', 'label' => 'Normal'],
            ['value' => 'low', 'label' => 'Low'],
        ];
        // Apply date filters (start_date / end_date) if provided. Respect selected financial year
        // by intersecting the requested range with the FY range. If the intersection is empty,
        // return no results.
        $selectedFyId = session('selected_financial_year', null);
        $fyFrom = null;
        $fyTo = null;
        if ($selectedFyId) {
            $fy = \App\Models\FinancialYear::find($selectedFyId);
            if ($fy) {
                $fyFrom = \Carbon\Carbon::parse($fy->from_date)->startOfDay();
                $fyTo = \Carbon\Carbon::parse($fy->to_date)->endOfDay();
            }
        }

        try {
            $start = $reqStart ? \Carbon\Carbon::parse($reqStart)->startOfDay() : null;
        } catch (\Exception $e) {
            $start = null;
        }
        try {
            $end = $reqEnd ? \Carbon\Carbon::parse($reqEnd)->endOfDay() : null;
        } catch (\Exception $e) {
            $end = null;
        }

        if ($start || $end) {
            if ($fyFrom) {
                if ($start && $start->lt($fyFrom)) $start = $fyFrom;
                if (!$start) $start = $fyFrom;
            }
            if ($fyTo) {
                if ($end && $end->gt($fyTo)) $end = $fyTo;
                if (!$end) $end = $fyTo;
            }

            if ($start && $end && $start->gt($end)) {
                $query->whereRaw('0 = 1');
            } else {
                if ($start && $end) {
                    $query->whereBetween('created_at', [$start, $end]);
                } elseif ($start) {
                    $query->where('created_at', '>=', $start);
                } elseif ($end) {
                    $query->where('created_at', '<=', $end);
                }
            }
        } else {
            if ($fyFrom && $fyTo) {
                $query->whereBetween('created_at', [$fyFrom, $fyTo]);
            }
        }

        $deals = $query->orderByDesc('created_at')->paginate(15)->appends(request()->except('page'));
        return view('deals.index', compact('deals', 'stages', 'priorities', 'user', 'leadSources'));
    }

    public function createFromLead($leadId)
    {
        if (!auth()->user()->hasCrmPermission('convert_crm_leads_to_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent creating deals when viewing an old/closed financial year
        $selectedFyId = session('selected_financial_year', null);
        if ($selectedFyId) {
            $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
            if ($activeFy && $selectedFyId != $activeFy->id) {
                return redirect()->back()->with('error', 'Selected financial year is closed. Creating deals is disabled for historical years.');
            }
        }

        $lead = Lead::with(['customer', 'organization', 'person', 'owner'])->findOrFail($leadId);
        $leadSources = \App\Models\LeadSource::orderBy('name')->get();
        $users = \App\Models\User::orderBy('name')->get();
        $stages = \App\Models\Stage::orderBy('order_by')->orderBy('order_by')->get();
        $categories = \App\Models\ProductCategory::where('status', 1)->orderBy('category_name')->get();
        return view('deals.convertDeal', compact('lead', 'leadSources', 'users', 'stages', 'categories'));
    }

    public function storeFromLead(Request $request, $leadId)
    {
         if (!auth()->user()->hasCrmPermission('convert_crm_leads_to_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent storing deals when viewing an old/closed financial year
        $selectedFyId = session('selected_financial_year', null);
        if ($selectedFyId) {
            $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
            if ($activeFy && $selectedFyId != $activeFy->id) {
                return redirect()->back()->with('error', 'Selected financial year is closed. Creating deals is disabled for historical years.');
            }
        }

        // Normalize stage input for case-insensitive checks
        $stageRaw = $request->input('stage', '');
        $isClosedLost = is_string($stageRaw) && strtolower(trim($stageRaw)) === 'closed lost';

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric',
            'label' => 'nullable|string',
            'lead_source' => 'nullable|string',
            'user_owner_id' => 'nullable|exists:users,id',
            'stage' => 'required|string',
            'probability' => 'nullable|integer|min:0|max:100',
            'close_date' => 'required|date',
            'reason_for_loss' => $isClosedLost ? 'required|string|max:255' : 'nullable|string|max:255',
        ]);
        // Load the lead early so we can perform duplicate checks using its organization/person
        $lead = Lead::findOrFail($leadId);

        // Duplicate prevention: title + organization + person
        $titleToCheck = $validated['title'] ?? $lead->getAttribute('title');
        $orgId = $lead->getAttribute('organization_id');
        $peopleId = $lead->getAttribute('people_id');
        if ($titleToCheck && $orgId && $peopleId) {
            $exists = \App\Models\Deal::whereRaw('LOWER(title) = ?', [strtolower($titleToCheck)])
                ->where('organization_id', $orgId)
                ->where('people_id', $peopleId)
                ->exists();
            if ($exists) {
                return redirect()->back()->withInput()->withErrors(['title' => 'A deal with the same title, company and contact person already exists.']);
            }
        }

        try {
            \DB::transaction(function () use ($request, $leadId, $validated, $lead) {

                // Determine status based on stage
                $stageLower = isset($validated['stage']) ? strtolower($validated['stage']) : '';
                $status = 'open';
                if ($stageLower === 'closed won') {
                    $status = 'Closed won';
                } elseif ($stageLower === 'closed lost') {
                    $status = 'Closed lost';
                }

                $ownerId = $validated['user_owner_id'] ?? $lead->getAttribute('user_owner_id');

                $deal = Deal::create([
                    'title' => $validated['title'] ?? $lead->getAttribute('title'),
                    'description' => $validated['description'] ?? $lead->getAttribute('description'),
                    'amount' => $validated['amount'] ?? $lead->getAttribute('amount'),
                    'probability' => $validated['probability'] ?? 10,
                    'label' => $validated['label'] ?? $lead->getAttribute('label'),
                    'lead_source' => $validated['lead_source'] ?? $lead->getAttribute('lead_source'),
                    'category' => $lead->getAttribute('category'),
                    'organization_id' => $lead->getAttribute('organization_id'),
                    'customer_id' => $lead->getAttribute('customer_id'),
                    'people_id' => $lead->getAttribute('people_id'),
                    'user_owner_id' => $ownerId,
                    'assigned_id' => $lead->getAttribute('assigned_id'),
                    'stage' => $validated['stage'] ?? null,
                    'close_date' => $validated['close_date'] ?? null,
                    'status' => $status,
                    'reason_for_loss' => $validated['reason_for_loss'] ?? null,
                    'created_by' => $lead->getAttribute('created_by'),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'converted_lead_id' => $lead->id,
                ]);

                // If converted to Closed Won, update monthly sales for the owner
                if ($status === 'Closed won' && $deal->user_owner_id && $deal->amount) {
                    $user = \App\Models\User::find($deal->user_owner_id);
                    $salesTarget = $user ? ($user->sales_target ?? 0) : 0;
                    $closeDate = $deal->close_date ? \Carbon\Carbon::parse($deal->close_date) : now();
                    $year = $closeDate->year;
                    $month = $closeDate->month;
                    \App\Models\UserMonthlySales::updateOrCreate(
                        [
                            'user_id' => $deal->user_owner_id,
                            'year' => $year,
                            'month' => $month
                        ],
                        [
                            'achieved_sales' => DB::raw('achieved_sales + ' . ($deal->amount ?? 0)),
                            'sales_target' => $salesTarget
                        ]
                    );
                }

                $lead->update(['converted_at' => now()]);
            });
        } catch (\Exception $e) {
            Log::error('Error converting lead to deal', ['lead_id' => $leadId, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Unable to convert lead to deal due to an internal error. Please contact admin.');
        }
        return redirect()->route('deals.index')->with('success', 'Lead converted to deal successfully');
    }

    public function create()
    {
        if (!auth()->user()->hasCrmPermission('create_crm_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }
        // Prevent creating deals when viewing an old/closed financial year
        $selectedFyId = session('selected_financial_year', null);
        if ($selectedFyId) {
            $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
            if ($activeFy && $selectedFyId != $activeFy->id) {
                return redirect()->back()->with('error', 'Selected financial year is closed. Creating deals is disabled for historical years.');
            }
        }
        $leadSources = \App\Models\LeadSource::orderBy('name')->get();
        $stages = \App\Models\Stage::orderBy('order_by')->orderBy('order_by')->get();
        $users = \App\Models\User::orderBy('name')->get();
        $currentUser = auth()->user();
        $organizations = \App\Models\Organization::orderBy('name')->get(); // <-- Add this line

        // Fetch active categories for the form
        $categories = \App\Models\ProductCategory::where('status', 1)->orderBy('category_name')->get();
        return view('deals.create', compact( 'currentUser', 'leadSources', 'stages', 'users', 'organizations', 'categories'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('create_crm_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent storing deals when viewing an old/closed financial year
        $selectedFyId = session('selected_financial_year', null);
        if ($selectedFyId) {
            $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
            if ($activeFy && $selectedFyId != $activeFy->id) {
                return redirect()->back()->with('error', 'Selected financial year is closed. Creating deals is disabled for historical years.');
            }
        }

        $messages = [
            'organization_id.required' => 'Please select or enter a company name',
            'organization_id.string' => 'Company name must be text',
            'organization_id.max' => 'Company name may not exceed 255 characters',
            'customer_id.string' => 'Company owner name must be text',
            'customer_id.max' => 'Company owner name may not exceed 255 characters',
            'people_id.required' => 'Please select or enter a contact person',
            'people_id.string' => 'Contact person name must be text',
            'people_id.max' => 'Contact person name may not exceed 255 characters',
            'amount.required' => 'Please enter the deal amount',
            'amount.numeric' => 'Deal amount must be a valid number',
            'probability.integer' => 'Probability must be a whole number',
            'probability.min' => 'Probability must be between 0 and 100',
            'probability.max' => 'Probability must be between 0 and 100',
            'label.required' => 'Please select a priority level',
            'label.string' => 'Priority must be selected from the list',
            'title.required' => 'Please enter the deal title',
            'title.string' => 'Deal title must be text',
            'title.max' => 'Deal title may not exceed 255 characters',
            'description.string' => 'Description must be text',
            'lead_source.required' => 'Please select a lead source',
            'lead_source.string' => 'Lead source must be selected from the list',
            'user_owner_id.required' => 'Please select an account owner',
            'user_owner_id.exists' => 'Selected account owner does not exist',
            'stage.required' => 'Please select a deal stage',
            'stage.string' => 'Deal stage must be selected from the list',
            'close_date.required' => 'Please select an expected close date',
            'close_date.date' => 'Expected close date must be a valid date',
            'categories.required' => 'Please select at least one category.',
            'categories.array' => 'Categories must be an array.',
            'categories.*.exists' => 'Selected category is invalid.',
        ];

        $stageRaw = $request->input('stage', '');
        $isClosedLost = is_string($stageRaw) && strtolower(trim($stageRaw)) === 'closed lost';

        $validated = $request->validate([
            'organization_id' => 'required|string|max:255',
            'customer_id' => 'nullable|string|max:255',
            'people_id' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'status' => 'nullable|string',
            'label' => 'required|string',

            'title' => 'required|string|max:255',
            'description' => 'nullable|string',

            'lead_source' => 'required|string',
            'user_owner_id' => 'required|exists:users,id',
            'stage' => 'required|string',
            'probability' => 'nullable|integer|min:0|max:100',
            'close_date' => 'required|date',
            'reason_for_loss' => $isClosedLost ? 'required|string|max:255' : 'nullable|string|max:255',

            'categories' => 'required|array',
            'categories.*' => 'exists:product_categories,id',
        ], $messages);

        // --- Organization ---
        $organizationId = null;
        if (!empty($validated['organization_id'])) {
             $org = \App\Models\Organization::whereRaw('LOWER(name) = ?', [strtolower($validated['organization_id'])])->first();
            if ($org) {
                $organizationId = $org->id;
            } else {
                $org = \App\Models\Organization::create([
                    'name' => $validated['organization_id'],
                    'industry_type' => 1, // default or null
                    'organization_type' => 1, // default or null
                    'user_owner_id' => $validated['user_owner_id'] ?? null,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'updated_by' => auth()->id(),
                ]);
                $organizationId = $org->id;
            }
        }

        // --- Customer ---
        $customerId = null;
        if (!empty($validated['customer_id'])) {
            $customer = \App\Models\Customer::whereRaw('LOWER(name) = ?', [strtolower($validated['customer_id'])])->where('organization_id', $organizationId)->first();
            if ($customer) {
                $customerId = $customer->id;
            } else {
                $customer = \App\Models\Customer::create([
                    'name' => $validated['customer_id'],
                    'organization_id' => $organizationId,
                    'user_owner_id' => $validated['user_owner_id'] ?? null,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'updated_by' => auth()->id(),
                ]);
                $customerId = $customer->id;
            }
        }
        // --- Person ---
        $peopleId = null;
        if (!empty($validated['people_id'])) {
             $personName = trim($validated['people_id']);
            $parts = explode(' ', $personName, 2); // limit to 2 parts only
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? null;
            // Try to fetch by first_name and last_name
            $query = \App\Models\Person::whereRaw('LOWER(first_name) = ?', [strtolower($firstName)]);
            if ($lastName) {
                $query->whereRaw('LOWER(last_name) = ?', [strtolower($lastName)]);
            }
            $person = $query->where('organization_id', $organizationId)->first();
            if ($person) {
                $peopleId = $person->id;
            } else {
                $person = \App\Models\Person::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'organization_id' => $organizationId,
                    'user_owner_id' => $validated['user_owner_id'] ?? null,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'updated_by' => auth()->id(),
                ]);
                $peopleId = $person->id;
            }
        }

        // Server-side duplicate prevention: title + organization + person
        if (!empty($validated['title']) && $organizationId && $peopleId) {
            $exists = \App\Models\Deal::whereRaw('LOWER(title) = ?', [strtolower($validated['title'])])
                ->where('organization_id', $organizationId)

                ->exists();
            if ($exists) {
                return redirect()->back()->withInput()->withErrors(['title' => 'A deal with the same title, company and contact person already exists.']);
            }
        }


        // Determine status from provided stage (so creating with Closed Won/Lost sets status correctly)
        $stageLowerCreate = is_string($validated['stage']) ? strtolower(trim($validated['stage'])) : '';
        $statusToSave = 'open';
        if ($stageLowerCreate === 'closed won') {
            $statusToSave = 'Closed won';
        } elseif ($stageLowerCreate === 'closed lost') {
            $statusToSave = 'Closed lost';
        }

        $deal = Deal::create( [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'] ?? null,
            'probability' => $validated['probability'] ?? 10,
            'label' => $validated['label'] ?? null,
            'lead_source' => $validated['lead_source'] ?? null,
            'category' => implode(',', $validated['categories']), // Store categories as a comma-separated string
            'user_owner_id' => $validated['user_owner_id'] ?? null,
            'stage' => $validated['stage'] ?? null,
            'status' => $statusToSave,
            'close_date' => $validated['close_date'] ?? null,
            'assigned_id' => $validated['user_owner_id'] ?? null,
            'organization_id' => $organizationId,
            'customer_id' => $customerId,
            'people_id' => $peopleId,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
            'updated_by' => auth()->id(),
        ]);
        // If created as Closed Won, update monthly sales for the owner
        if ($statusToSave === 'Closed won' && $deal->user_owner_id && $deal->amount) {
            $user = \App\Models\User::find($deal->user_owner_id);
            $salesTarget = $user ? ($user->sales_target ?? 0) : 0;
            $closeDate = $deal->close_date ? \Carbon\Carbon::parse($deal->close_date) : now();
            $year = $closeDate->year;
            $month = $closeDate->month;
            \App\Models\UserMonthlySales::updateOrCreate(
                [
                    'user_id' => $deal->user_owner_id,
                    'year' => $year,
                    'month' => $month
                ],
                [
                    'achieved_sales' => DB::raw('achieved_sales + ' . ($deal->amount ?? 0)),
                    'sales_target' => $salesTarget
                ]
            );
        }
        return redirect()->route('deals.index')->with('success', 'Deal created successfully');
    }

    /**
     * AJAX: Check if a deal with the same title + organization + contact person already exists.
     * Returns JSON { duplicate: true|false }
     */
    public function checkDuplicate(Request $request)
    {
        $title = trim($request->get('title', ''));
        $orgInput = $request->get('organization', '');
        $personInput = $request->get('person', '');

        if ($title === '' || $orgInput === '' || $personInput === '') {
            return response()->json(['duplicate' => false]);
        }

        // Resolve organization id (accept numeric id or name)
        $organizationId = null;
        if (is_numeric($orgInput)) {
            $organizationId = intval($orgInput);
        } else {
            $org = \App\Models\Organization::whereRaw('LOWER(name) = ?', [strtolower($orgInput)])->first();
            if ($org) $organizationId = $org->id;
        }

        // Resolve person id (accept numeric id or "First Last" name)
        $peopleId = null;
        if (is_numeric($personInput)) {
            $peopleId = intval($personInput);
        } else {
            $parts = explode(' ', trim($personInput), 2);
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? null;
            $personQuery = \App\Models\Person::whereRaw('LOWER(first_name) = ?', [strtolower($firstName)]);
            if ($lastName) {
                $personQuery->whereRaw('LOWER(last_name) = ?', [strtolower($lastName)]);
            }
            if ($organizationId) {
                $personQuery->where('organization_id', $organizationId);
            }
            $person = $personQuery->first();
            if ($person) $peopleId = $person->id;
        }

        if (!$organizationId || !$peopleId) {
            return response()->json(['duplicate' => false]);
        }

        $exists = \App\Models\Deal::whereRaw('LOWER(title) = ?', [strtolower($title)])
            ->where('organization_id', $organizationId)
            ->where('people_id', $peopleId)
            ->exists();

        return response()->json(['duplicate' => $exists]);
    }

    public function edit($id)
    {
        if (!auth()->user()->hasCrmPermission('edit_crm_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }
        // Prevent editing deals when viewing an old/closed financial year
        $selectedFyId = session('selected_financial_year', null);
        if ($selectedFyId) {
            $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
            if ($activeFy && $selectedFyId != $activeFy->id) {
                return redirect()->back()->with('error', 'Selected financial year is closed. Editing deals is disabled for historical years.');
            }
        }

        $deal = Deal::with(['customer', 'organization', 'person'])->findOrFail($id);
        $leadSources = \App\Models\LeadSource::orderBy('name')->get();
        $stages = \App\Models\Stage::orderBy('order_by')->orderBy('order_by')->get();
        $users = \App\Models\User::orderBy('name')->get();
        $organizations = \App\Models\Organization::orderBy('name')->get(); // <-- Add this line
        $categories = \App\Models\ProductCategory::where('status', 1)->orderBy('category_name')->get();
        return view('deals.edit', compact('deal', 'leadSources', 'stages', 'users', 'organizations', 'categories'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasCrmPermission('edit_crm_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent updating deals when viewing an old/closed financial year
        $selectedFyId = session('selected_financial_year', null);
        if ($selectedFyId) {
            $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
            if ($activeFy && $selectedFyId != $activeFy->id) {
                return redirect()->back()->with('error', 'Selected financial year is closed. Editing deals is disabled for historical years.');
            }
        }

        $messages = [
            'organization_id.required' => 'Please select or enter a company name',
            'organization_id.string' => 'Company name must be text',
            'organization_id.max' => 'Company name may not exceed 255 characters',
            'customer_id.string' => 'Company owner name must be text',
            'customer_id.max' => 'Company owner name may not exceed 255 characters',
            'people_id.required' => 'Please select or enter a contact person',
            'people_id.string' => 'Contact person name must be text',
            'people_id.max' => 'Contact person name may not exceed 255 characters',
            'amount.required' => 'Please enter the deal amount',
            'amount.numeric' => 'Deal amount must be a valid number',
            'probability.integer' => 'Probability must be a whole number',
            'probability.min' => 'Probability must be between 0 and 100',
            'probability.max' => 'Probability must be between 0 and 100',
            'label.required' => 'Please select a priority level',
            'label.string' => 'Priority must be selected from the list',
            'title.required' => 'Please enter the deal title',
            'title.string' => 'Deal title must be text',
            'title.max' => 'Deal title may not exceed 255 characters',
            'description.string' => 'Description must be text',
            'lead_source.required' => 'Please select a lead source',
            'lead_source.string' => 'Lead source must be selected from the list',
            'user_owner_id.required' => 'Please select an account owner',
            'user_owner_id.exists' => 'Selected account owner does not exist',
            'stage.required' => 'Please select a deal stage',
            'stage.string' => 'Deal stage must be selected from the list',
            'close_date.required' => 'Please select an expected close date',
            'close_date.date' => 'Expected close date must be a valid date',
            'categories.required' => 'Please select at least one category.',
            'categories.array' => 'Categories must be an array.',
            'categories.*.exists' => 'Selected category is invalid.',
        ];

        // Removed debug code causing memory exhaustion
        $stageRaw = $request->input('stage', '');
        $isClosedLost = is_string($stageRaw) && strtolower(trim($stageRaw)) === 'closed lost';

        $validated = $request->validate([
            'organization_id' => 'required|string|max:255',
            'customer_id' => 'nullable|string|max:255',
            'people_id' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'status' => 'nullable|string',
            'label' => 'required|string',

            'title' => 'required|string|max:255',
            'description' => 'nullable|string',

            'lead_source' => 'required|string',
            'user_owner_id' => 'required|exists:users,id',
            'stage' => 'required|string',
            'probability' => 'nullable|integer|min:0|max:100',
            'close_date' => 'required|date',
            'reason_for_loss' => $isClosedLost ? 'required|string|max:255' : 'nullable|string|max:255',
            'categories' => 'required|array',
            'categories.*' => 'exists:product_categories,id',
        ], $messages);
        // $deal = Deal::findOrFail($id);
        // $deal->update($validated + [
        //     'updated_at' => now(),
        // ]);

        // --- Organization ---
        $organizationId = null;
        if (!empty($validated['organization_id'])) {
             $org = \App\Models\Organization::whereRaw('LOWER(name) = ?', [strtolower($validated['organization_id'])])->first();
            if ($org) {
                $organizationId = $org->id;
            } else {
                $org = \App\Models\Organization::create([
                    'name' => $validated['organization_id'],
                    'industry_type' => 1, // default or null
                    'organization_type' => 1, // default or null
                    'user_owner_id' => $validated['user_owner_id'] ?? null,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'updated_by' => auth()->id(),
                ]);
                $organizationId = $org->id;
            }
        }

        // --- Customer ---
        $customerId = null;
        if (!empty($validated['customer_id'])) {
            $customer = \App\Models\Customer::whereRaw('LOWER(name) = ?', [strtolower($validated['customer_id'])])->where('organization_id', $organizationId)->first();
            if ($customer) {
                $customerId = $customer->id;
            } else {
                $customer = \App\Models\Customer::create([
                    'name' => $validated['customer_id'],
                    'organization_id' => $organizationId,
                    'user_owner_id' => $validated['user_owner_id'] ?? null,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'updated_by' => auth()->id(),
                ]);
                $customerId = $customer->id;
            }
        }
        // --- Person ---
        $peopleId = null;
        if (!empty($validated['people_id'])) {
             $personName = trim($validated['people_id']);
            $parts = explode(' ', $personName, 2); // limit to 2 parts only
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? null;
            // Try to fetch by first_name and last_name
            $query = \App\Models\Person::whereRaw('LOWER(first_name) = ?', [strtolower($firstName)]);
            if ($lastName) {
                $query->whereRaw('LOWER(last_name) = ?', [strtolower($lastName)]);
            }
            $person = $query->where('organization_id', $organizationId)->first();
            if ($person) {
                $peopleId = $person->id;
            } else {
                $person = \App\Models\Person::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'organization_id' => $organizationId,
                    'user_owner_id' => $validated['user_owner_id'] ?? null,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'updated_by' => auth()->id(),
                ]);
                $peopleId = $person->id;
            }
        }
        $deal = Deal::findOrFail($id);
        $status = $validated['status'] ?? $deal->status;
        $stageChanged = isset($validated['stage']) && $validated['stage'] !== $deal->stage;
        $wasClosedWon = strtolower($deal->stage) === 'closed won';
        $isClosedWon = isset($validated['stage']) && strtolower($validated['stage']) === 'closed won';
        if (isset($validated['stage'])) {
            if ($isClosedWon) {
                $status = 'Closed won';
            } elseif (strtolower($validated['stage']) === 'closed lost') {
                $status = 'Closed lost';
            }
        }
        $deal->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'] ?? null,
            'probability' => $validated['probability'] ?? 10,
            'label' => $validated['label'] ?? null,
            'lead_source' => $validated['lead_source'] ?? null,
            'category' => implode(',', $validated['categories']), // Store categories as a comma-separated string,
            'user_owner_id' => $validated['user_owner_id'] ?? null,
            'stage' => $validated['stage'] ?? null,
            'status' => $status,
            'close_date' => $validated['close_date'] ?? null,
            'assigned_id' => $validated['user_owner_id'] ?? null,
            'organization_id' => $organizationId,
            'customer_id' => $customerId,
            'people_id' => $peopleId,
            'reason_for_loss' => $validated['reason_for_loss'] ?? null,
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);
        // Record stage change in stage_history if changed
        if ($stageChanged) {
            \App\Models\StageHistory::create([
                'deal_id' => $deal->id,
                'stage_name' => $validated['stage'],
                'amount' => $deal->amount,
                'probability' => $deal->probability,
                'close_date' => $deal->close_date,
                'modified_time' => now(),
                'modified_by' => auth()->id(),
            ]);
            // Only update monthly sales if moving to Closed Won and was not already Closed Won
            if ($isClosedWon && !$wasClosedWon && $deal->user_owner_id && $deal->amount) {
                    $closeDate = $deal->close_date ? \Carbon\Carbon::parse($deal->close_date) : now();
                    $year = $closeDate->year;
                    $month = $closeDate->month;
                    $user = \App\Models\User::find($deal->user_owner_id);
                    $salesTarget = $user ? ($user->sales_target ?? 0) : 0;
                    \App\Models\UserMonthlySales::updateOrCreate(
                        [
                            'user_id' => $deal->user_owner_id,
                            'year' => $year,
                            'month' => $month
                        ],
                        [
                            'achieved_sales' => \DB::raw('achieved_sales + ' . ($deal->amount ?? 0)),
                            'sales_target' => $salesTarget
                        ]
                    );
            }
        }
        return redirect()->route('deals.index')->with('success', 'Deal updated successfully');
    }

    public function show($id)
    {
        if (!auth()->user()->hasCrmPermission('view_crm_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $users = User::all();
        $deal = Deal::with(['owner'])->findOrFail($id);
        $leads = Lead::all();
        // Fetch category names for each lead
        $categoryIds = explode(',', $deal->category ?? '');
        $deal->category_names = \App\Models\ProductCategory::whereIn('id', $categoryIds)->pluck('category_name')->toArray();
       
        $meetings = \App\Models\Meeting::where('related_type', 'deal')->where('related_id', $deal->id)->orderByDesc('start_at')->get();
                    //participants list for each meeting
        foreach ($meetings as $meeting) {
            $meeting->related_name = $meeting->related_type === 'lead' ? $meeting->lead->title : ($meeting->related_type === 'deal' ? $meeting->deal->title : null);
            $meeting->participants_list = $meeting->participants()->with('user')->get()->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'type' => $participant->type,
                    'name' => $participant->type === 'user' ? $participant->user->name : $participant->contact->first_name,
                ];
            });
            $meeting->participants_names = $meeting->participants_list->pluck('name')->implode(', ');
            $meeting->participants_lists = $meeting->participants()->with('user')->get()->map(function ($participant) {
                return [
                    'id' => $participant->user_id,
                    'type' => $participant->type
                ];
            })->toArray();
        }
         $contacts = Contact::all();
        return view('deals.show', compact('deal', 'users', 'leads', 'meetings', 'contacts'));
    }

    public function showConvertDeals($id)
    {
        if (!auth()->user()->hasCrmPermission('view_crm_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $users = User::all();
        $deal = Deal::with(['owner'])
        ->where('converted_lead_id', $id)
        ->firstOrFail();
        // Fetch category names for each lead
        $categoryIds = explode(',', $deal->category ?? '');
        $deal->category_names = \App\Models\ProductCategory::whereIn('id', $categoryIds)->pluck('category_name')->toArray();
        return view('deals.show', compact('deal', 'users'));
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasCrmPermission('delete_crm_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }
        // Prevent deleting deals when viewing an old/closed financial year
        $selectedFyId = session('selected_financial_year', null);
        if ($selectedFyId) {
            $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
            if ($activeFy && $selectedFyId != $activeFy->id) {
                return redirect()->back()->with('error', 'Selected financial year is closed. Deleting deals is disabled for historical years.');
            }
        }
        $deal = Deal::findOrFail($id);
        $deal->delete();
        return redirect()->route('deals.index')->with('success', 'Deal deleted successfully');
    }

    // ...existing code...

    public function markWon($id)
    {
        if (!auth()->user()->hasCrmPermission('won_crm_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }
        // Prevent marking as won when viewing an old/closed financial year
        $selectedFyId = session('selected_financial_year', null);
        if ($selectedFyId) {
            $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
            if ($activeFy && $selectedFyId != $activeFy->id) {
                return redirect()->back()->with('error', 'Selected financial year is closed. Modifications are disabled for historical years.');
            }
        }
        $deal = Deal::findOrFail($id);
        $deal->update([
            'status' => 'Closed won',
            'stage' => 'Closed Won',
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);
            // Update achieved_sales for the deal owner (total and monthly)
            if ($deal->user_owner_id && $deal->amount) {
                $user = User::find($deal->user_owner_id);
                $salesTarget = $user ? ($user->sales_target ?? 0) : 0;
                if ($user) {
                    // Update monthly sales
                        $closeDate = $deal->close_date ? \Carbon\Carbon::parse($deal->close_date) : now();
                        $year = $closeDate->year;
                        $month = $closeDate->month;
                    \App\Models\UserMonthlySales::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'year' => $year,
                            'month' => $month
                        ],
                        [
                            'achieved_sales' => DB::raw('achieved_sales + ' . ($deal->amount ?? 0)),
                            'sales_target' => $salesTarget
                        ]
                    );
                }
            }
        return redirect()->route('deals.index')->with('success', 'Deal marked as won.');
    }

    public function markLost($id)
    {
        if (!auth()->user()->hasCrmPermission('lost_crm_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }
        // Prevent marking as lost when viewing an old/closed financial year
        $selectedFyId = session('selected_financial_year', null);
        if ($selectedFyId) {
            $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
            if ($activeFy && $selectedFyId != $activeFy->id) {
                return redirect()->back()->with('error', 'Selected financial year is closed. Modifications are disabled for historical years.');
            }
        }
        $deal = Deal::findOrFail($id);
        $deal->update([
            'status' => 'Closed lost',
            'stage' => 'Closed Lost',
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);
        return redirect()->route('deals.index')->with('success', 'Deal marked as lost.');
    }

    public function reopen($id)
    {
        if (!auth()->user()->hasCrmPermission('reopen_crm_deals_guard')) {
            abort(403, 'Unauthorized action.');
        }
        // Prevent reopening when viewing an old/closed financial year
        $selectedFyId = session('selected_financial_year', null);
        if ($selectedFyId) {
            $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
            if ($activeFy && $selectedFyId != $activeFy->id) {
                return redirect()->back()->with('error', 'Selected financial year is closed. Modifications are disabled for historical years.');
            }
        }
        $deal = Deal::findOrFail($id);
        $deal->update([
            'status' => 'open',
            'stage' => 'Qualification',
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);
        return redirect()->route('deals.index')->with('success', 'Deal reopened.');
    }

    public function updateStage(Request $request)
    {
        $deal = \App\Models\Deal::findOrFail($request->deal_id);
        $stage = \App\Models\Stage::find($request->stage_id);
        if ($stage) {
            $oldStage = $deal->stage;
            $oldProbability = $deal->probability;
            $deal->stage = $stage->name;
            // Update probability if stage has a probability value
            if (!is_null($stage->probability)) {
                $deal->probability = $stage->probability;
            }
            // Handle reason_for_loss
            if (strtolower($stage->name) === 'closed lost') {
                $deal->reason_for_loss = $request->input('reason_for_loss', '');
                $deal->status = 'Closed lost';
            } elseif (strtolower($stage->name) === 'closed won') {
                $deal->reason_for_loss = null;
                $deal->status = 'Closed won';
                // Accept optional amount and close_date when marking won via pipeline
                if ($request->filled('amount')) {
                    $deal->amount = $request->input('amount');
                }
                if ($request->filled('close_date')) {
                    $deal->close_date = $request->input('close_date');
                }
                    // Update achieved_sales for the deal owner (total and monthly)
                    if ($deal->user_owner_id && $deal->amount) {
                        $user = User::find($deal->user_owner_id);
                        $salesTarget = $user ? ($user->sales_target ?? 0) : 0;
                        if ($user) {
                             // Update monthly sales
                            $closeDate = $deal->close_date ? \Carbon\Carbon::parse($deal->close_date) : now();
                            $year = $closeDate->year;
                            $month = $closeDate->month;
                            \App\Models\UserMonthlySales::updateOrCreate(
                                [
                                    'user_id' => $user->id,
                                    'year' => $year,
                                    'month' => $month
                                ],
                                [
                                    'achieved_sales' => DB::raw('achieved_sales + ' . ($deal->amount ?? 0)),
                                    'sales_target' => $salesTarget
                                ]
                            );
                        }
                    }
            } else {
                $deal->reason_for_loss = null;
            }
            $deal->updated_by = auth()->id();
            $deal->updated_at = now();
            $deal->save();
            $newStage = $deal->stage;
            $newProbability = $deal->probability;
            // Record stage change in stage_history
            \App\Models\StageHistory::create([
                'deal_id' => $deal->id,
                'stage_name' => $deal->stage,
                'amount' => $deal->amount,
                'probability' => $deal->probability,
                'close_date' => $deal->close_date,
                'modified_time' => now(),
                'modified_by' => auth()->id(),
            ]);
            Log::info('Deal stage updated', [
                'deal_id' => $deal->id,
                'old_stage' => $oldStage,
                'new_stage' => $newStage,
                'old_probability' => $oldProbability,
                'new_probability' => $newProbability,
                'reason_for_loss' => $deal->reason_for_loss,
            ]);
            return response()->json([
                'success' => true,
                'old_stage' => $oldStage,
                'new_stage' => $newStage,
                'old_probability' => $oldProbability,
                'new_probability' => $newProbability
            ]);
        } else {
            Log::warning('Stage not found for update', [
                'deal_id' => $request->deal_id,
                'stage_id' => $request->stage_id
            ]);
            return response()->json(['success' => false, 'message' => 'Stage not found']);
        }
    }

    public function getDealDetails()
    {
        echo 'df';
    }

    public function stageHistory($dealId)
    {
        $history = \App\Models\StageHistory::with('user')
            ->where('deal_id', $dealId)
            ->orderByDesc('modified_time')
            ->get();
        return view('deals.stage_history', compact('history'));
    }
}
