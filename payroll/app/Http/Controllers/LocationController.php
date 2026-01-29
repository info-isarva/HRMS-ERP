<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use App\Services\ActivityLogService;

class LocationController extends Controller
{
    // Index - Manage Locations
    public function index()
    {
        $locations = Location::latest()->get();
        return view('masters.locations.index', compact('locations'));
    }    

    // Store Location
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations',
            'short_name' => 'nullable|string|max:50|unique:locations',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ], [
            'name.unique' => 'A location with this name already exists.',
            'short_name.unique' => 'A location with this short name already exists.',
        ]);

        Location::create($validated);

        // Log location creation
        ActivityLogService::log(
            'location_create',
            'Created location',
            "Created location: {$validated['name']}",
            [
                'location_name' => $validated['name'],
                'status' => $validated['status']
            ]
        );

        return redirect()->route('form/location/manage')
            ->with('success', 'Location created successfully');
    }

    
    public function getById($id)
    {
        $location = Location::findOrFail($id);
        return response()->json($location);
    }

    // Update Location
    public function update(Request $request)
    {
        $location = Location::findOrFail($request->id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,' . $location->id,
            'short_name' => 'nullable|string|max:50|unique:locations,short_name,' . $location->id,
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ], [
            'name.unique' => 'A location with this name already exists.',
            'short_name.unique' => 'A location with this short name already exists.',
        ]);

        $location->update($validated);

        // Log location update
        ActivityLogService::log(
            'location_update',
            'Updated location',
            "Updated location: {$validated['name']}",
            [
                'location_id' => $location->id,
                'location_name' => $validated['name'],
                'status' => $validated['status']
            ]
        );

        return redirect()->route('form/location/manage')->with('success', 'Location updated successfully.');
    }

    // Delete Location
    public function destroy(Request $request)
    {
        $location = Location::findOrFail($request->id);
        
        // Log location deletion
        ActivityLogService::log(
            'location_delete',
            'Deleted location',
            "Deleted location: {$location->name}",
            [
                'location_id' => $location->id,
                'location_name' => $location->name,
                'short_name' => $location->short_name,
                'description' => $location->description,
                'status' => $location->status
            ]
        );
        
        $location->delete();
        return redirect()->route('form/location/manage')
            ->with('success', 'Location deleted successfully');
    }
}
