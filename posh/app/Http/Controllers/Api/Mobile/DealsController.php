<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;

class DealsController extends Controller
{
    // Deals related APIs will be implemented here in the future
    public function index(Request $request)
    {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or missing token',
                ], 401);
            }

            // Build base query based on role
            $query = Deal::query();
            if ($user->crm_role_type === 0 || $user->crm_role_type === 1) {
                // Admin/Super Admin: all deals
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

            // Filters
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
            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', date('Y-m-d', strtotime($request->input('from_date'))));
            }
            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', date('Y-m-d', strtotime($request->input('to_date'))));
            }

            $deals = $query->orderBy('stage', 'asc')->get();

           

            return response()->json([
                'success' => true,
                'deals' => $deals,
            ]);
    }

    // Deal detail api
    public function show($id)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);    
        }
        $deal = Deal::find($id);
        if (!$deal) {
            return response()->json([
                'success' => false,
                'message' => 'Deal not found',
            ], 404);        
        }
        // Prepare related names (assuming relationships are defined in Deal model)
        $dealData = [
            'id' => $deal->id,
            'title' => $deal->title,
            'organization_id' => $deal->organization_id,
            'organization' => $deal->organization->name ?? null,
            'customer_id' => $deal->customer_id,
            'customer' =>  $deal->customer->name ?? null,
            'people_id' => $deal->people_id,
            'person' => $deal->person->id ? trim($deal->person->first_name. ' ' . $deal->person->last_name) : null,
            'amount' => $deal->amount,
            'status' => $deal->status,
            'reason_for_loss' => $deal->reason_for_loss,
            'label' => $deal->label,
            'expected_close' => $deal->expected_close,
            'converted_at' => $deal->converted_at,
            'description' => $deal->description,
            'lead_source_id' => $deal->lead_source,
            'lead_source' => $deal->leadSource->name ?? null,
            'category' => $deal->category ?? null,
            'assigned_id' => $deal->assigned_id,
            'assigned_name' => \App\Models\User::find($deal->assigned_id)->name ?? null,
            'user_owner_id' => $deal->user_owner_id,
            'owner' => $deal->owner->name ?? null,
            'created_by' => $deal->created_by,
            'created_by_name' => \App\Models\User::find($deal->created_by)->name ?? null,
            'created_at' => $deal->created_at,
            'updated_at' => $deal->updated_at,
            'updated_by' => $deal->updated_by,
            'deleted_by' => $deal->deleted_by,
            'deleted_at' => $deal->deleted_at,
        ];
        return response()->json([
            'success' => true,
            'deal' => $dealData,
        ]);
    }

    //Add deal api 
    public function store(Request $request)
    {
        // Implementation for adding a new deal will go here
        // You can add validation and logic here
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }
        
        // Validate request data
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
            // 'status' => 'nullable|string',
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

        $organizationId = $validated['organization_id'];
        $peopleId = $validated['people_id'];
        // Server-side duplicate prevention: title + organization + person
        if (!empty($validated['title']) && $organizationId && $peopleId) {
            $existingDeal = Deal::where('title', $validated['title'])
                ->where('organization_id', $validated['organization_id'])
                ->where('people_id', $validated['people_id'])
                ->first();
            if ($existingDeal) {
                return response()->json([
                    'success' => false,
                    'message' => 'A deal with the same title, organization, and contact person already exists.',
                ], 409); // Conflict
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
        
        // Logic to create and save the new deal will go here
        $deal = new Deal();
        $deal->organization_id = $validated['organization_id'];
        $deal->customer_id = $validated['customer_id'] ?? null;
        $deal->people_id = $validated['people_id'];
        $deal->amount = $validated['amount'];
        $deal->status = $statusToSave;
        $deal->label = $validated['label'];
        $deal->title = $validated['title'];
        $deal->description = $validated['description'] ?? null;
        $deal->lead_source = $validated['lead_source'];
        $deal->category = implode(',', $validated['categories']); // Store as comma-separated string
        $deal->user_owner_id = $validated['user_owner_id']; 
        $deal->stage = $validated['stage'];
        $deal->probability = $validated['probability'] ?? null;
        $deal->close_date = date('Y-m-d', strtotime($validated['close_date']));
        $deal->reason_for_loss = $validated['reason_for_loss'] ?? null;
        $deal->created_by = $user->id;
        $deal->save();

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
        
        return response()->json([
            'success' => true,
            'message' => 'Deal created successfully',
            'deal_id' => $deal->id,
        ], 201);


    }

    //Update deal api
    public function update(Request $request, $id)
    {
        // Implementation for updating an existing deal will go here
        // You can add validation and logic here
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }

        // Further implementation will go here
        $deal = Deal::find($id);
        if (!$deal) {
            return response()->json([
                'success' => false,
                'message' => 'Deal not found',
            ], 404);        
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

        $deal->organization_id = $validated['organization_id'];
        $deal->customer_id = $validated['customer_id'] ?? null;
        $deal->people_id = $validated['people_id'];    
        $deal->amount = $validated['amount'];
        $deal->status = $status;
        $deal->label = $validated['label'];
        $deal->title = $validated['title'];
        $deal->description = $validated['description'] ?? null; 
        $deal->lead_source = $validated['lead_source'];
        $deal->category = implode(',', $validated['categories']); // Store as comma-separated string
        $deal->user_owner_id = $validated['user_owner_id']; 
        $deal->stage = $validated['stage'];
        $deal->probability = $validated['probability'] ?? null;
        $deal->close_date = date('Y-m-d', strtotime($validated['close_date']));
        $deal->reason_for_loss = $validated['reason_for_loss'] ?? null;
        $deal->updated_by = $user->id;
        $deal->updated_at = now();
        $deal->save();

        // Record stage change in stage_history if changed
        if ($stageChanged) {
            \App\Models\StageHistory::create([
                'deal_id' => $deal->id,
                'stage_name' => $validated['stage'],
                'amount' => $deal->amount,
                'probability' => $deal->probability,
                'close_date' => $deal->close_date,
                'modified_time' => now(),
                'modified_by' => $user->id,
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
        return response()->json([
            'success' => true,
            'message' => 'Deal updated successfully',
            'deal_id' => $deal->id,
        ]);

    }

    //convert leads into deals api
    public function storeFromLead(Request $request, $leadId)
    {

        
        // Implementation for converting a lead into a deal will go here
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
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
                return response()->json([
                    'success' => false,
                    'message' => 'A deal with the same title, organization, and contact person already exists.',
                ], 409); // Conflict
            }
        }

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
                'close_date' => Carbon::parse($validated['close_date'])   ?? null,
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

        return response()->json([
            'success' => true,
            'message' => 'Lead converted to deal successfully',
        ]);


    }

    // Delete deal api
    public function destroy($id)
    {
        // Implementation for deleting a deal will go here
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }
        $deal = Deal::find($id);
        if (!$deal) {
            return response()->json([
                'success' => false,
                'message' => 'Deal not found',
            ], 404);        
        }
        $deal->deleted_by = $user->id;
        $deal->save();
        $deal->delete();    

        return response()->json([
            'success' => true,
            'message' => 'Deal deleted successfully',
        ]);
    }

}