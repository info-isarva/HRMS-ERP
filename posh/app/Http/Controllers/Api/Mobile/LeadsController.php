<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadsController extends Controller
{
    //
    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }

        $leads = []; // Placeholder for leads data

        $query = Lead::query();
        if ($user->crm_role_type == 0 || $user->crm_role_type == 1) {
            // Admin/Super Admin: all leads
        } else {
            $isManager = \App\Models\User::where('assign_manager', $user->id)->exists();
            if ($isManager) {
                $teamUserIds = \App\Models\User::where('assign_manager', $user->id)->pluck('id')->toArray();
                $query->where(function($q) use ($user, $teamUserIds) {
                    $q->orWhere('created_by', $user->id)
                      ->orWhere('assigned_id', $user->id)
                      ->orWhereIn('created_by', $teamUserIds)
                      ->orWhereIn('assigned_id', $teamUserIds);
                });
            } else {
                $query->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_id', $user->id);
                });
            }
        }
        // Additional filters can be applied here based on request parameters
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }
        if ($request->filled('category')) {
            $category = $request->input('category');
            if ($category) {
                $query->whereRaw("FIND_IN_SET(?, category)", [$category]);
            }
        }
        if ($request->filled('lead_source')) {
            $leadSource = $request->input('lead_source');
            if (is_array($leadSource)) {
                $query->whereIn('lead_source', $leadSource);
            } else {
                $query->where('lead_source', $leadSource);
            }
        }
        if ($request->filled('status')) {
            $status = $request->input('status');
            if (is_array($status)) {
                $query->whereIn('status', $status);
            } else {
                $query->where('status', $status);
            }
        }
        if($request->filled('priority')){
            $priority = $request->input('priority');
            if (is_array($priority)) {
                $query->whereIn('label', $priority);
            } else {
                $query->where('label', $priority);
            }  
        }
        if($request->filled('owner')){
            $owner = $request->input('owner');
            if (is_array($owner)) {
                $query->whereIn('user_owner_id', $owner);
            } else {
                $query->where('user_owner_id', $owner);
            }  
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', date('Y-m-d', strtotime($request->input('from_date'))));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', date('Y-m-d', strtotime($request->input('to_date'))));
        }

        $view = $request->input('view', 'All Leads');
        // View filter
        switch ($view) {
            case 'Converted Leads':
                $query->whereNotNull('converted_at');
                break;
            case 'Junk Leads':
                $query->where('status', 'Junk Lead');
                break;
            case 'Mailing Labels':
                $query->whereNotNull('mailing_label'); // Adjust if you have a different field
                break;
            case 'My Converted Leads':
                $query->whereNotNull('converted_at')->where('user_owner_id', $user->id);
                break;
            case 'My Leads':
                $query->where('user_owner_id', $user->id);
                break;
            case 'Not Qualified Leads':
                $query->where('status', 'not_qualified');
                break;
            case 'Open Leads':
                $query->whereNotIn('status', ['Lost Lead'])
                    ->whereNull('converted_at');
                break;
            case 'Recently Created Leads':
                $query->orderBy('created_at', 'desc');
                break;
            case 'Recently Modified Leads':
                $query->orderBy('updated_at', 'desc');
                break;
            case "Today's Leads":
                $query->whereDate('created_at', now()->toDateString());
                break;
            case 'Unread Leads':
                $query->where('is_read', false); // Add this field if needed
                break;
            case 'Unsubscribed Leads':
                $query->where('is_unsubscribed', true); // Add this field if needed
                break;
            case 'All Leads':
            default:
                // No additional filter
                break;
        }

         // Default: show only unconverted unless view is Converted Leads/My Converted Leads
        if (!in_array($view, ['Converted Leads', 'My Converted Leads'])) {
            $query->whereNull('converted_at');
        }
        $leads = $query->get(); // You can modify this to fetch actual leads

        //Add People name and other related names only name displayed
        $leads->transform(function ($lead) {
            return [
                'id' => $lead->id,
                'title' => $lead->title,
                'organization_id' => $lead->organization_id,
                'organization_name' => $lead->organization->name ?? null,
                'customer_id' => $lead->customer_id,
                'customer_name' => $lead->customer->name ?? null,
                'people_id' => $lead->people_id,
                'person_name' => $lead->person->id ? trim($lead->person->first_name. ' ' . $lead->person->last_name) : null,
                'amount' => $lead->amount,
                'status' => $lead->status,
                'reason_for_loss' => $lead->reason_for_loss,
                'label' => $lead->label,
                'expected_close' => $lead->expected_close,
                'converted_at' => $lead->converted_at,
                'description' => $lead->description,
                'lead_source_id' => $lead->lead_source,
                'lead_source' => $lead->leadSource->name ?? null,
                'category' => $lead->category ?? null,
                'assigned_id' => $lead->assigned_id,
                'assigned_name' => \App\Models\User::find($lead->assigned_id)->name ?? null,
                'user_owner_id' => $lead->user_owner_id,
                'owner' => $lead->owner->name ?? null,
                'created_by' => $lead->created_by,
                'created_by_name' => \App\Models\User::find($lead->created_by)->name ?? null,
                'created_at' => $lead->created_at,
                'updated_at' => $lead->updated_at,
                'updated_by' => $lead->updated_by,
                'deleted_by' => $lead->deleted_by,
                'deleted_at' => $lead->deleted_at,
            ];
        });
        

        return response()->json([
            'success' => true,
            'leads' => $leads,
        ]);
    }

    //Leads detail api
    public function show($id)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);    
        }

        // Prepare related names (assuming relationships are defined in Lead model)
        $leadData = [
            'id' => $lead->id,
            'title' => $lead->title,
            'organization_id' => $lead->organization_id,
            'organization' => $lead->organization->name ?? null,
            'customer_id' => $lead->customer_id,
            'customer' => $lead->customer->name ?? null,
            'people_id' => $lead->people_id,
            'person' => $lead->person->id ? trim($lead->person->first_name. ' ' . $lead->person->last_name) : null,
            'amount' => $lead->amount,
            'status' => $lead->status,
            'reason_for_loss' => $lead->reason_for_loss,
            'label' => $lead->label,
            'expected_close' => $lead->expected_close,
            'converted_at' => $lead->converted_at,
            'description' => $lead->description,
            'lead_source_id' => $lead->lead_source,
            'lead_source' => $lead->leadSource->name ?? null,
            'category' => $lead->category ?? null,
            'assigned_id' => $lead->assigned_id,
            'assigned_name' => \App\Models\User::find($lead->assigned_id)->name ?? null,
            'user_owner_id' => $lead->user_owner_id,
            'owner' => $lead->owner->name ?? null,
            'created_by' => $lead->created_by,
            'created_by_name' => \App\Models\User::find($lead->created_by)->name ?? null,
            'created_at' => $lead->created_at,
            'updated_at' => $lead->updated_at,
            'updated_by' => $lead->updated_by,
            'deleted_by' => $lead->deleted_by,
            'deleted_at' => $lead->deleted_at,
        ];

        return response()->json([
            'success' => true,
            'lead' => $leadData,
        ]);
    }

    //Create Lead api
    public function store(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);    
        }
        // Validate request data
         $messages = [
            'title.required' => 'Please enter a title for the lead.',
            'title.max' => 'Title may not be longer than 100 characters.',
            'organization_id.required' => 'Please select or enter the Company name',
            'organization_id.max' => 'Company name may not exceed 255 characters.',
            'people_id.required' => 'Please select or add a Contact Person.',
            'people_id.max' => 'Contact person value is too long.',
            'user_owner_id.required' => 'Please assign an Owner.',
            'user_owner_id.exists' => 'The selected Owner is invalid.',
            'lead_source.required' => 'Please select a Lead Source.',
            'label.required' => 'Please choose a Priority for this lead.',
            'status.required' => 'Please select a Lead Status.',
            'description.max' => 'Description may not exceed 250 characters.',
            'amount.numeric' => 'Amount must be a valid number.',

            'categories.required' => 'Please select at least one category.',
            'categories.array' => 'Categories must be an array.',
            'categories.*.exists' => 'Selected category is invalid.',
        ];

        $validatedData = $request->validate([
            'title'           => 'required|string|max:100',
            'organization_id' => 'required|string|max:255',
            'people_id' => 'required|string|max:255',
            'customer_id' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|max:50000000',
            'status' => 'required|string',
            'label' => 'required|string',
            'lead_source' => 'required|string',
            'user_owner_id' => 'required|exists:users,id',
            'description' => 'nullable|string|max:250',
            'categories' => 'required|array',
            'categories.*' => 'exists:product_categories,id',
            
        ], $messages);

        //Server-side duplicate prevention: title + organization + person
        $organizationId = $validatedData['organization_id'];
        $peopleId = $validatedData['people_id'];
        if(!empty($validatedData['title']) && $organizationId && $peopleId) {
            $existingLead = Lead::where('title', $validatedData['title'])
                ->where('organization_id', $validatedData['organization_id'])
                ->where('people_id', $validatedData['people_id'])
                ->first();
            if ($existingLead) {
                return response()->json([
                    'success' => false,
                    'message' => 'A lead with the same Title, Company, and Contact Person already exists.',
                ], 409); // Conflict
            }
        }

        // Create new Lead
        $lead = new Lead();
        $lead->title = $validatedData['title'];
        $lead->organization_id = $validatedData['organization_id'];
        $lead->people_id = $validatedData['people_id'];
        $lead->customer_id = $validatedData['customer_id'] ?? null;
        $lead->amount = $validatedData['amount'] ?? null;
        $lead->status = $validatedData['status'];
        $lead->label = $validatedData['label'];
        $lead->lead_source = $validatedData['lead_source'];
        $lead->user_owner_id = $validatedData['user_owner_id'];
        $lead->assigned_id = $validatedData['user_owner_id']; // Assign to owner by default
        $lead->description = $validatedData['description'] ?? null;
        $lead->category = implode(',', $validatedData['categories']);
        $lead->created_by = $user->id;
        $lead->save();
        return response()->json([
            'success' => true,
            'message' => 'Lead created successfully',
            'lead_id' => $lead->id,
        ], 201);
    }

    //Update Lead api
    public function update(Request $request, $id)
    {
        // Similar to store method, but for updating an existing lead
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);    
        }
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);   
        }
        // Validate request data
        $messages = [
            'title.required' => 'Please enter a title for the lead.',
            'title.max' => 'Title may not be longer than 100 characters.',
            'organization_id.required' => 'Please select or enter the Company name',
            'organization_id.max' => 'Company name may not exceed 255 characters.',
            'people_id.required' => 'Please select or add a Contact Person.',
            'people_id.max' => 'Contact person value is too long.',
            'user_owner_id.required' => 'Please assign an Owner.',
            'user_owner_id.exists' => 'The selected Owner is invalid.',
            'lead_source.required' => 'Please select a Lead Source.',
            'label.required' => 'Please choose a Priority for this lead.',
            'status.required' => 'Please select a Lead Status.',
            'description.max' => 'Description may not exceed 250 characters.',
            'amount.numeric' => 'Amount must be a valid number.',
            'categories.required' => 'Please select at least one category.',
            'categories.array' => 'Categories must be an array.',
            'categories.*.exists' => 'Selected category is invalid.',
        ];
        $validatedData = $request->validate([
            'title'           => 'required|string|max:100',
            'organization_id' => 'required|string|max:255',
            'people_id' => 'required|string|max:255',
            'customer_id' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|max:50000000',
            'status' => 'required|string',
            'label' => 'required|string',
            'lead_source' => 'required|string',
            'user_owner_id' => 'required|exists:users,id',
            'description' => 'nullable|string|max:250',
            'categories' => 'required|array',
            'categories.*' => 'exists:product_categories,id',
        ], $messages);  
        // Update Lead
        $lead->title = $validatedData['title'];
        $lead->organization_id = $validatedData['organization_id'];
        $lead->people_id = $validatedData['people_id'];
        $lead->customer_id = $validatedData['customer_id'] ?? null;
        $lead->amount = $validatedData['amount'] ?? null;
        $lead->status = $validatedData['status'];
        $lead->label = $validatedData['label'];
        $lead->lead_source = $validatedData['lead_source'];
        $lead->user_owner_id = $validatedData['user_owner_id'];
        $lead->assigned_id = $validatedData['user_owner_id'];
        $lead->description = $validatedData['description'] ?? null;
        $lead->category = implode(',', $validatedData['categories']);
        $lead->updated_by = $user->id;
        $lead->updated_at = now();
        $lead->save();
        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully',
        ]); 
    }
    
    //Delete Lead api
    public function destroy($id)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);

        }
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        }
        $lead->deleted_by = $user->id;
        $lead->save();
        $lead->delete();
        return response()->json([
            'success' => true,
            'message' => 'Lead deleted successfully',
        ]);
    }
}