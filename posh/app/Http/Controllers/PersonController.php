<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PeopleExport;

class PersonController extends Controller
{
    public function __construct()
    {
        // Prevent creating/editing/deleting people when a historical financial year is selected.
        // Use the middleware class directly to avoid alias resolution issues.
        $this->middleware(\App\Http\Middleware\PreventHistoricalFinancialYear::class)->only([
            'create', 'savePeople', 'store', 'ajaxCreate', 'edit', 'update', 'destroy', 'deleteContact', 'addOrganization'
        ]);
    }
     // People index page
    public function index()
    {
        if (!auth()->user()->hasCrmPermission('manage_crm_contact_person_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $view = request('view', 'All Contacts');
        $user = auth()->user();
        $query = \App\Models\Person::with('owner');

        switch ($view) {
            case 'All Contacts':
                // No additional filter
                break;
            case 'Mailing Labels':
                $query->whereNotNull('mailing_label'); // Adjust if you have a different field
                break;
            case 'My Contacts':
                $query->where('user_owner_id', $user->id);
                break;
            case 'New Last Week':
                $query->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
                break;
            case 'New This Week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'Recently Created Contacts':
                $query->orderBy('created_at', 'desc');
                break;
            case 'Recently Modified Contacts':
                $query->orderBy('updated_at', 'desc');
                break;
            case 'Unread Contacts':
                $query->where('is_read', false); // Add this field if needed
                break;
            case 'Unsubscribed Contacts':
                $query->where('is_unsubscribed', true); // Add this field if needed
                break;
            default:
                // No additional filter
                break;
        }

        // Individual filters (name, email, mobile, owner)
        $nameFilter = trim(request('name', ''));
        $emailFilter = trim(request('email', ''));
        $mobileFilter = trim(request('mobile', ''));
        $ownerFilter = request('owner_id', null);

        if ($nameFilter !== '') {
            $query->where(function($q) use ($nameFilter) {
                $q->where('first_name', 'like', '%' . $nameFilter . '%')
                  ->orWhere('last_name', 'like', '%' . $nameFilter . '%');
            });
        }

        if ($emailFilter !== '') {
            $query->where('email', 'like', '%' . $emailFilter . '%');
        }

        if ($mobileFilter !== '') {
            $query->where('mobile', 'like', '%' . $mobileFilter . '%');
        }

        if (!empty($ownerFilter)) {
            $query->where('user_owner_id', $ownerFilter);
        }

        // Fallback global search using 'q' param (keeps backward compatibility)
        $search = trim(request('q', ''));
        if ($search) {
            $searchLower = strtolower($search);
            
            // Split search into words for first/last name matching
            $searchWords = explode(' ', $search);
            $firstWord = $searchWords[0] ?? '';
            $remainingWords = implode(' ', array_slice($searchWords, 1));
            
            $query->where(function($q) use ($searchLower, $firstWord, $remainingWords) {
                // Exact match on full name (first + last)
                $q->whereRaw('LOWER(first_name) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$searchLower}%"]);
                
                // Split name search: first word matches first_name, remaining matches last_name
                if (!empty($firstWord) && !empty($remainingWords)) {
                    $firstWordLower = strtolower($firstWord);
                    $remainingLower = strtolower($remainingWords);
                    $q->orWhere(function($sub) use ($firstWordLower, $remainingLower) {
                        $sub->whereRaw('LOWER(first_name) LIKE ?', ["%{$firstWordLower}%"])
                            ->whereRaw('LOWER(last_name) LIKE ?', ["%{$remainingLower}%"]);
                    });
                }
                
                // Email or mobile search
                $q->orWhereRaw('LOWER(email) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(mobile) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereHas('owner', function($oq) use ($searchLower) {
                      $oq->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"]);
                  });
            });
        }

    $people = $query->orderBy('first_name')->paginate(15)->appends(request()->except('page'));
    $contacts = \App\Models\Person::all(); // Fetch all contacts

        return view('people.index', compact('people', 'contacts'));
    }

    // AJAX autocomplete for person (contact) names
    public function autocomplete(Request $request)
    {
        $search = $request->get('q', '');
        $orgName = trim($request->get('organization', ''));
        $query = Person::query();
        if ($orgName) {
            $org = \App\Models\Organization::whereRaw('LOWER(name) = ?', [strtolower($orgName)])->first();
            if ($org) {
                $query->where('organization_id', $org->id);
            }
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%');
            });
        }
        $results = $query->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        return response()->json($results);
    }

    // AJAX: Get person details by name and (optionally) organization
    public function details(Request $request)
    {
        $name = $request->get('name', '');
        $orgName = trim($request->get('organization', ''));
        $query = Person::query();
        if ($orgName) {
            $org = \App\Models\Organization::whereRaw('LOWER(name) = ?', [strtolower($orgName)])->first();
            if ($org) {
                $query->where('organization_id', $org->id);
            }
        }
        if ($name) {
            $query->where('first_name', $name);
        }
        $person = $query->first();
        if ($person) {
            return response()->json([
                'phone' => $person->phone,
                'mobile' => $person->mobile,
                'email' => $person->email,
            ]);
        } else {
            return response()->json(['phone' => '', 'mobile' => '', 'email' => '']);
        }
    }

       // Show person details
    public function show($id)
    {
        if (!auth()->user()->hasCrmPermission('view_crm_contact_person_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $person = \App\Models\Person::with('owner', 'organization')->findOrFail($id);
        return view('people.show', compact('person'));
    }

    public function create()
    {
        if (!auth()->user()->hasCrmPermission('create_crm_contact_person_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $owners = \App\Models\User::orderBy('name')->get();
        $currentUserId = auth()->id();
        $leadSources = \App\Models\LeadSource::orderBy('name')->get();
        return view('people.create', compact('owners', 'currentUserId', 'leadSources'));
    }

    public function savePeople(Request $request) {
         if (!auth()->user()->hasCrmPermission('create_crm_contact_person_guard')) {
            abort(403, 'Unauthorized action.');
        }
         $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255', 'regex:/^\w+$/'],
            'last_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z .\'-]*$/'],
            'gender' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:people,email',
                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^\+?[0-9\-\s]{10,20}$/',
            ],
            'mobile' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+?[0-9\-\s]{10,20}$/',
                'unique:people,mobile',
            ],
            'job_title' => 'nullable|string|max:255',
            'lead_source' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'owner_id' => 'required|integer|exists:users,id',
        ], [
            'first_name.regex' => 'First name must be a single word.',
            'last_name.regex' => 'Last name may only contain letters, spaces, dots, apostrophes, and hyphens.',
            'email.regex' => 'Please enter a valid email address.',
            'phone.regex' => 'Please enter a valid phone number.',
            'mobile.regex' => 'Please enter a valid mobile number.',
        ]);
        $person = Person::create($validated + [
            'user_owner_id' => $request->input('owner_id'),
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
            'updated_by' => auth()->id(),
        ]);
        return redirect()->route('people.show', $person->id)->with('success', 'Person added successfully!');
    }

    // Store a new person (AJAX from modal)
    public function store(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('create_crm_contact_person_guard')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255|regex:/^\w+$/',
                'last_name' => 'nullable|string|max:255|regex:/^[A-Za-z .\'-]*$/',
                'mobile' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9\-\s]{10,20}$/', 'unique:people,mobile'],
                'email' => 'nullable|email|max:255|unique:people,email',
                'organization_id' => 'required|integer|exists:organizations,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
        $person = new Person();
        $person->first_name = $validated['first_name'];
        $person->last_name = $validated['last_name'] ?? '';
        $person->mobile = $validated['mobile'] ?? null;
        $person->email = $validated['email'] ?? null;
        $person->organization_id = $validated['organization_id'];
        $person->user_owner_id =  auth()->id();
        $person->created_by = auth()->id();
        $person->updated_by = auth()->id();
        $person->save();
        return response()->json(['success' => true, 'id' => $person->id]);
    }

    // Delete a person (AJAX)
    public function destroy($id)
    {
        if (!auth()->user()->hasCrmPermission('delete_crm_contact_person_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $person = Person::find($id);
        if (!$person) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Contact Person not found.'], 404);
            }
            return redirect()->route('people.index')->with('error', 'Contact Person not found.');
        }

        // Check for related leads or deals
        $hasRelatedRecords = $person->leads()->exists() || $person->deals()->exists();
        if ($hasRelatedRecords) {
            $message = 'Unable to delete the record. This person has related leads or deals.';
            // if (request()->wantsJson() || request()->ajax()) {
            //     return response()->json(['success' => false, 'message' => $message], 400);
            // }
            // Add SweetAlert message for non-AJAX requests
            return redirect()->route('people.index')->with('error', $message)->with('sweetalert', [
                'title' => 'Deletion Blocked',
                'text' => $message,
                'icon' => 'error',
                'confirmButtonText' => 'OK'
            ]);
        }

        $person->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('people.index')->with('success', 'Contact Person and related records deleted successfully!');
    }

    /**
     * Export people to Excel using the same filters as index.
     */
    public function exportExcel(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('manage_crm_contact_person_guard')) {
            abort(403, 'Unauthorized action.');
        }

        $view = $request->get('view', 'All Contacts');
        $user = auth()->user();
        $query = \App\Models\Person::with(['owner', 'organization']);

        switch ($view) {
            case 'All Contacts':
                break;
            case 'Mailing Labels':
                $query->whereNotNull('mailing_label');
                break;
            case 'My Contacts':
                $query->where('user_owner_id', $user->id);
                break;
            case 'New Last Week':
                $query->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
                break;
            case 'New This Week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'Recently Created Contacts':
                $query->orderBy('created_at', 'desc');
                break;
            case 'Recently Modified Contacts':
                $query->orderBy('updated_at', 'desc');
                break;
            case 'Unread Contacts':
                $query->where('is_read', false);
                break;
            case 'Unsubscribed Contacts':
                $query->where('is_unsubscribed', true);
                break;
            default:
                break;
        }

        // Individual filters (name, email, mobile, owner)
        $nameFilter = trim($request->get('name', ''));
        $emailFilter = trim($request->get('email', ''));
        $mobileFilter = trim($request->get('mobile', ''));
        $ownerFilter = $request->get('owner_id', null);

        if ($nameFilter !== '') {
            $query->where(function($q) use ($nameFilter) {
                $q->where('first_name', 'like', '%' . $nameFilter . '%')
                  ->orWhere('last_name', 'like', '%' . $nameFilter . '%');
            });
        }

        if ($emailFilter !== '') {
            $query->where('email', 'like', '%' . $emailFilter . '%');
        }

        if ($mobileFilter !== '') {
            $query->where('mobile', 'like', '%' . $mobileFilter . '%');
        }

        if (!empty($ownerFilter)) {
            $query->where('user_owner_id', $ownerFilter);
        }

        // Fallback global search
        $q = trim($request->get('q', ''));
        if ($q !== '') {
            $term = $q;
            $query->where(function($sub) use ($term) {
                $sub->where('first_name', 'like', '%' . $term . '%')
                    ->orWhere('last_name', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%')
                    ->orWhere('mobile', 'like', '%' . $term . '%')
                    ->orWhereHas('owner', function($oq) use ($term) {
                        $oq->where('name', 'like', '%' . $term . '%');
                    });
            });
        }

        $people = $query->orderBy('first_name')->get();

        $rows = [];
        foreach ($people as $p) {
            $rows[] = [
                $p->first_name,
                $p->last_name,
                $p->email ?? '-',
                $p->mobile ?? '-',
                optional($p->owner)->name ?? '-',
                optional($p->organization)->name ?? '-',
                $p->created_at ? $p->created_at->toDateTimeString() : '-',
                $p->updated_at ? $p->updated_at->toDateTimeString() : '-',
            ];
        }

        $fileName = 'people_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new PeopleExport($rows), $fileName);
    }

    public function deleteContact($id)
    {
        if (!auth()->user()->hasCrmPermission('delete_crm_contact_person_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $person = Person::find($id);
        if (!$person) {
            return response()->json(['success' => false, 'message' => 'Person not found.'], 404);
        }

        // Check for related leads or deals
        $hasRelatedRecords = $person->leads()->exists() || $person->deals()->exists();
        if ($hasRelatedRecords) {
            $message = 'Unable to delete the record. This person has related leads or deals.';
            // if (request()->wantsJson() || request()->ajax()) {
            //     return response()->json(['success' => false, 'message' => $message], 400);
            // }
            return redirect()->route('people.index')->with('error', $message)->with('sweetalert', [
                'title' => 'Deletion Blocked',
                'text' => $message,
                'icon' => 'error',
                'confirmButtonText' => 'OK'
            ]);
        }
        $person->delete();
        return redirect()->route('people.index')->with('success', 'Person deleted successfully!');
    }

    // List people for a customer (AJAX)
    public function listForCustomer($id)
    {
        $customer = \App\Models\Customer::find($id);
        if (!$customer) {
            return response()->json([]);
        }
        $people = $customer->people()->orderBy('first_name')->get(['id', 'first_name']);
        return response()->json($people);
    }

    // List people for an organization (AJAX)
    public function listForOrganization($id)
    {
        $org = \App\Models\Organization::find($id);
        if (!$org) {
            return response()->json([]);
        }
        $people = $org->people()->orderBy('first_name')->get(['id', 'first_name']);
        return response()->json($people);
    }

    // AJAX: Add or link organization to person from modal
    public function addOrganization(Request $request, $personId)
    {
        $person = \App\Models\Person::findOrFail($personId);
        $orgId = $request->input('organization_id');
        $orgName = trim($request->input('organization_name'));
        $organization = null;

        if ($orgId) {
            $organization = \App\Models\Organization::find($orgId);
            if (!$organization) {
                return response()->json(['success' => false, 'message' => 'Organization not found.'], 404);
            }
        } elseif ($orgName) {
            $organization = \App\Models\Organization::whereRaw('LOWER(name) = ?', [strtolower($orgName)])->first();
            if (!$organization) {
                $organization = \App\Models\Organization::create([
                    'name' => $orgName,
                    'industry_type' => 1, // Default to 1 (or set as needed)
                    'organization_type' => 1, // Default to 1 (or set as needed)
                    'created_by' => auth()->id(),
                    'user_owner_id' => auth()->id(),
                ]);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Organization name required.'], 422);
        }

        $person->organization_id = $organization->id;
        $person->save();
        return response()->json(['success' => true, 'organization' => $organization->name]);
    }

    // Show the form for editing a person
    public function edit($id)
    {
        if (!auth()->user()->hasCrmPermission('edit_crm_contact_person_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $person = \App\Models\Person::findOrFail($id);
        $owners = \App\Models\User::orderBy('name')->get();
        $leadSources = \App\Models\LeadSource::orderBy('name')->get();
        $organizations = \App\Models\Organization::orderBy('name')->get();
        return view('people.edit', compact('person', 'owners', 'leadSources', 'organizations'));
    }

    // Update person details
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasCrmPermission('edit_crm_contact_person_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255', 'regex:/^\w+$/'],
            'last_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z .\'-]*$/'],
            'gender' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:people,email,' . $id,
                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^\+?[0-9\-\s]{10,20}$/',
            ],
            'mobile' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+?[0-9\-\s]{10,20}$/',
                'unique:people,mobile,' . $id,
            ],
            'job_title' => 'nullable|string|max:255',
            'lead_source' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'owner_id' => 'required|integer|exists:users,id',
        ], [
            'first_name.regex' => 'First name may only contain letters, spaces, dots, apostrophes, and hyphens.',
            'last_name.regex' => 'Last name may only contain letters, spaces, dots, apostrophes, and hyphens.',
            'email.regex' => 'Please enter a valid email address.',
            'phone.regex' => 'Please enter a valid phone number.',
            'mobile.regex' => 'Please enter a valid mobile number.',
        ]);
        $person = \App\Models\Person::findOrFail($id);
        $person->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'mobile' => $validated['mobile'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'lead_source' => $validated['lead_source'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'user_owner_id' => $validated['owner_id'] ?? null,

            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);
        return redirect()->route('people.show', $person->id)->with('success', 'Person updated successfully!');
        // return redirect()->route('people.index')->with('success', 'Person updated successfully!');
    }

    // AJAX: Create new People (minimal fields)
    public function ajaxCreate(Request $request)
    {
        try {
             // Custom messages for AJAX person create
            $messages = [
                'person_first_name.required' => 'Please enter the first name.',
                'person_first_name.max' => 'First name may not exceed 255 characters.',
                'person_first_name.regex' => 'First name must be a single word.',

                'person_last_name.max' => 'Last name may not exceed 255 characters.',
                'person_last_name.regex' => 'Last name may only contain letters, spaces, dots, apostrophes, and hyphens.',

                'person_org_id.required' => 'Please select or enter the Company name',
                'person_org_id.integer' => 'Invalid organization selected.',
                'person_org_id.exists' => 'The selected organization does not exist.',

                'person_email.email' => 'Please enter a valid email address.',
                'person_email.max' => 'Email may not exceed 255 characters.',
                'person_email.regex' => 'Please enter a valid email address.',

                'person_phone.required' => 'Please enter a mobile number for the contact.',
                'person_phone.regex' => 'Please enter a valid mobile number.',
                'person_phone.max' => 'Mobile number may not exceed 20 characters.',
                'person_phone.unique' => 'This mobile number is already used by another contact.',
            ];
            // Validate the request
            $validated = $request->validate([
                'person_first_name' => ['required', 'string', 'max:255', 'regex:/^\w+$/'],
                'person_last_name' =>['nullable', 'string', 'max:255', 'regex:/^[A-Za-z .\'-]*$/'],
                'person_org_id' => 'required|integer|exists:organizations,id',
                'person_email' => 'nullable|email|max:255|regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',
                'person_phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9\-\s]{10,20}$/', 'unique:people,mobile'],
            ], $messages);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        // Server-side duplicate check: same first+last name under the same organization
        $existing = Person::where('organization_id', $validated['person_org_id'])
            ->whereRaw('LOWER(first_name) = ?', [strtolower($validated['person_first_name'])])
            ->whereRaw('LOWER(COALESCE(last_name, "")) = ?', [strtolower($validated['person_last_name'] ?? '')])
            ->first();
        if ($existing) {
            return response()->json([
                'duplicate' => true,
                'id' => $existing->id,
                'first_name' => $existing->first_name,
                'last_name' => $existing->last_name,
            ], 200);
        }

        $person = Person::create( [
            'first_name' => $validated['person_first_name'],
            'last_name' => $validated['person_last_name'] ?? null,
            'organization_id' => $validated['person_org_id'],
            'email' => $validated['person_email'] ?? null,
            'mobile' => $validated['person_phone'] ?? null,
            'user_owner_id' => auth()->id(),
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
            'updated_by' => auth()->id(),
        ]);
        return response()->json(['id' => $person->id, 'first_name' => $person->first_name, 'last_name' => $person->last_name]);
    }
}
