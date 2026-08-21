<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Person;

class ContactPersonController extends Controller
{
    /**
     * List contact persons for a given organization (requires valid token).
     */
    public function index(Request $request, $organizationId)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }

         $contacts = Person::where('organization_id', $organizationId)
            ->get()
            ->map(function ($person) {
                return [
                    'id' => $person->id,
                    'fullname' => trim($person->first_name . ' ' . $person->last_name),
                    'email' => $person->email,
                    'mobile' => $person->mobile,
                ];
            });
        return response()->json([
            'success' => true,
            'contacts' => $contacts,
        ]);
    }

    //List All Contact Persons  API
    public function listAllContacts(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }

         $contacts = Person::get()
            ->map(function ($person) {
                return [
                    'id' => $person->id,
                    'fullname' => trim($person->first_name . ' ' . $person->last_name),
                    'email' => $person->email,
                    'mobile' => $person->mobile,
                ];
            });
        return response()->json([
            'success' => true,
            'contacts' => $contacts,
        ]);
    }

    //Contact Person Details API
    public function show(Request $request, $contactId)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }

         $person = Person::find($contactId);
         if (!$person) {
            return response()->json([
                'success' => false,
                'message' => 'Contact person not found',
            ], 404);
         }

        //  $contactDetails = [
        //     'id' => $person->id,
        //     'first_name' => $person->first_name,
        //     'last_name' => $person->last_name,
        //     'email' => $person->email,
        //     'mobile' => $person->mobile,
        //     'phone' => $person->phone,
        //     'designation' => $person->designation,
        //     'address' => $person->address,
        //     // Add other fields as necessary
        //  ];

        $contactDetails = $person;

        return response()->json([
            'success' => true,
            'contact' => $contactDetails,
        ]);
    }
    
    //Create Contact Person API (Optional)
    public function store(Request $request)
    {
        // Implementation for creating a contact person
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }
        // Validate and create contact person logic here
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

        $person = new Person();
        $person->first_name = $validated['first_name'];
        $person->last_name = $validated['last_name'] ?? null;
        $person->gender = $validated['gender'] ?? null;
        $person->dob = $validated['dob'] ?? null;
        $person->email = $validated['email'] ?? null;
        $person->phone = $validated['phone'] ?? null;
        $person->mobile = $validated['mobile'];
        $person->job_title = $validated['job_title'] ?? null;
        $person->lead_source = $validated['lead_source'] ?? null;
        $person->address = $validated['address'] ?? null;
        $person->notes = $validated['notes'] ?? null;
        $person->user_owner_id = $validated['owner_id'];
        $person->created_by = $user->id;
        $person->save();

        return response()->json([
            'success' => true,
            'message' => 'Contact person created successfully',
            'contact' => $person->id,
        ], 201);
    }

    //Update Contact Person API (Optional)
    public function update(Request $request, $contactId)
    {
        // Implementation for updating a contact person
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }
        // Validate and update contact person logic here
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255', 'regex:/^\w+$/'],
            'last_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z .\'-]*$/'],
            'gender' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:people,email,' . $contactId,
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
                'unique:people,mobile,' . $contactId,
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
        $person = Person::find($contactId);
        if (!$person) {
            return response()->json([
                'success' => false,
                'message' => 'Contact person not found',
            ], 404);
        }
        $person->first_name = $validated['first_name'];
        $person->last_name = $validated['last_name'] ?? null;
        $person->gender = $validated['gender'] ?? null;
        $person->dob = $validated['dob'] ?? null;
        $person->email = $validated['email'] ?? null;
        $person->phone = $validated['phone'] ?? null;
        $person->mobile = $validated['mobile'];
        $person->job_title = $validated['job_title'] ?? null;
        $person->lead_source = $validated['lead_source'] ?? null;
        $person->address = $validated['address'] ?? null;
        $person->notes = $validated['notes'] ?? null;
        $person->user_owner_id = $validated['owner_id'];
        $person->save();

        return response()->json([
            'success' => true,
            'message' => 'Contact person updated successfully',
            'contact' => $person->id,
        ]);
    }

    //Delete Contact Person API (Optional)
    public function destroy(Request $request, $contactId)
    {
        // Implementation for deleting a contact person
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }

        $person = Person::find($contactId);
        if (!$person) {
            return response()->json([
                'success' => false,
                'message' => 'Contact person not found',
            ], 404);
        }

        // cross check assign any deals or leads before deleting (optional)
        // (Implementation for checking deals or leads would go here)
        if($person->deals()->count() > 0 || $person->leads()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete contact person assigned to deals or leads',
            ], 400);
        }

        $person->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact person deleted successfully',
        ]);
    }
}