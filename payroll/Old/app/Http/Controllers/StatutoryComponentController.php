<?php

namespace App\Http\Controllers;

use App\Models\StatutoryComponent;
use App\Models\Location;
use Illuminate\Http\Request;

class StatutoryComponentController extends Controller
{
    // Index - Manage Components
    public function index()
    {
        $components = StatutoryComponent::latest()->get();
        $locations = Location::active()->get();
        return view('salary-settings.statutory-components.index', compact('components', 'locations'));
    }

    // Create Form
    public function create()
    {
        return view('salary-settings.statutory-components.create');
    }

    // Store Component
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:statutory_components',
            'short_name' => 'required|string|max:50|unique:statutory_components',
            'type' => 'required|in:earning,deduction',
            'status' => 'required|boolean',
            'calculation_type' => 'nullable|string',
            'calculation_value' => 'nullable|numeric',
            'location_id' => 'required|array'
        ]);

        StatutoryComponent::create($validated);

        return redirect()->route('form/statutory-component/manage')
            ->with('success', 'Statutory component created successfully');
    }

    // Edit Form
    // public function edit(StatutoryComponent $statutoryComponent)
    // {
    //     return view('salary-settings.statutory-components.edit', compact('statutoryComponent'));
    // }

    public function getById($id)
    {
        $component = StatutoryComponent::findOrFail($id);
        return response()->json($component);
    }

    // Update Component
    public function update(Request $request)
    {
        $component = StatutoryComponent::findOrFail($request->id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:statutory_components,name,' . $request->id,
            'short_name' => 'required|string|max:50|unique:statutory_components,short_name,' . $request->id,
            'type' => 'required|in:earning,deduction',
            'status' => 'required|boolean',
            'calculation_type' => 'nullable|string',
            'calculation_value' => 'nullable|numeric',
            'location_id' => 'required|array'
        ]);

        $component->update($validated);

        return redirect()->route('form/statutory-component/manage')->with('success', 'Component updated.');
    }

    // Delete Component
    public function destroy(StatutoryComponent $statutoryComponent)
    {
        $statutoryComponent->delete();
        return redirect()->route('form/statutory-component/manage')
            ->with('success', 'Component deleted successfully');
    }
}
