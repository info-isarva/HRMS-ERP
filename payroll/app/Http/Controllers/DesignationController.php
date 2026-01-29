<?php

namespace App\Http\Controllers;

use App\Models\PositionType;
use Illuminate\Http\Request;
use App\Services\ActivityLogService;

class DesignationController extends Controller
{
    // Index - Manage Designations
    public function index()
    {
        $designations = PositionType::latest()->get();
        return view('masters.designations.index', compact('designations'));
    }    

    // Store Designation
    public function store(Request $request)
    {
        $validated = $request->validate([
            'position' => 'required|string|max:255|unique:position_types',
            'short_name' => 'nullable|string|max:50|unique:position_types',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ], [
            'position.unique' => 'A designation with this name already exists.',
            'short_name.unique' => 'A designation with this short name already exists.',
        ]);

        PositionType::create($validated);

        // Log designation creation
        ActivityLogService::log(
            'designation_create',
            'Created designation',
            "Created designation: {$validated['position']}",
            [
                'designation_name' => $validated['position'],
                //'short_name' => $validated['short_name'],
               // 'description' => $validated['description'],
                'status' => $validated['status']
            ]
        );

        return redirect()->route('form/designation/manage')
            ->with('success', 'Designation created successfully');
    }

    
    public function getById($id)
    {
        $designation = PositionType::findOrFail($id);
        return response()->json($designation);
    }

    // Update Designation
    public function update(Request $request)
    {
        $designation = PositionType::findOrFail($request->id);
        
        $validated = $request->validate([
            'position' => 'required|string|max:255|unique:position_types,position,' . $designation->id,
            'short_name' => 'nullable|string|max:50|unique:position_types,short_name,' . $designation->id,
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ], [
            'position.unique' => 'A designation with this name already exists.',
            'short_name.unique' => 'A designation with this short name already exists.',
        ]);

        $designation->update($validated);

        // Log designation update
        ActivityLogService::log(
            'designation_update',
            'Updated designation',
            "Updated designation: {$validated['position']}",
            [
                'designation_id' => $designation->id,
                'designation_name' => $validated['position'],
               // 'short_name' => $validated['short_name'],
                //'description' => $validated['description'],
                'status' => $validated['status']
            ]
        );

        return redirect()->route('form/designation/manage')->with('success', 'Designation updated successfully.');
    }

    // Delete Designation
    public function destroy(Request $request)
    {
        $designation = PositionType::findOrFail($request->id);
        
        // Log designation deletion
        ActivityLogService::log(
            'designation_delete',
            'Deleted designation',
            "Deleted designation: {$designation->position}",
            [
                'designation_id' => $designation->id,
                'designation_name' => $designation->position,
                'short_name' => $designation->short_name,
                'description' => $designation->description,
                'status' => $designation->status
            ]
        );
        
        $designation->delete();
        return redirect()->route('form/designation/manage')
            ->with('success', 'Designation deleted successfully');
    }
}
