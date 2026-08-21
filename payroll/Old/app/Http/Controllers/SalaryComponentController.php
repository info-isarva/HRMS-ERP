<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Services\ActivityLogService;

class SalaryComponentController extends Controller
{
    // Index - Manage Components
    public function index()
    {
        $components = SalaryComponent::latest()->get();
        $locations = Location::active()->get();
        return view('salary-settings.salary-components.index', compact('components', 'locations'));
    }    

    // Store Component
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:salary_components',
            'short_name' => 'required|string|max:50|unique:salary_components',
            'type' => 'required|in:earning,deduction',
            'status' => 'required|boolean',
            'calculation_type' => 'nullable|string',
            'calculation_value' => 'nullable|numeric',
            'is_residual' => 'nullable|boolean',
            'location_id' => 'required|array'
        ]);

        if ($request->has('is_residual') && $request->is_residual) {
            // Reset other residuals if this one is set
            SalaryComponent::where('is_residual', true)->update(['is_residual' => false]);
            $validated['calculation_type'] = 'residual'; // Force type if flag is set, for consistency
        }

        $component = SalaryComponent::create($validated);

        // Log the activity
        ActivityLogService::log(
            'salary_component_create',
            'Created salary component',
            "Created new salary component: {$component->name} ({$component->short_name})",
            [
                'component_id' => $component->id,
                'name' => $component->name,
                'short_name' => $component->short_name,
                'type' => $component->type,
                'status' => $component->status,
                'calculation_type' => $component->calculation_type,
                'calculation_value' => $component->calculation_value,
                'is_residual' => $component->is_residual,
                'location_id' => $component->location_id
            ]
        );

        return redirect()->route('form/salary-component/manage')
            ->with('success', 'Salary component created successfully');
    }

    
    public function getById($id)
    {
        $component = SalaryComponent::findOrFail($id);
        return response()->json($component);
    }

    // Update Component
    public function update(Request $request)
    {
        $component = SalaryComponent::findOrFail($request->id);
        
        // Store old values for logging
        $oldData = $component->toArray();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:salary_components,name,' . $request->id,
            'short_name' => 'required|string|max:50|unique:salary_components,short_name,' . $request->id,
            'type' => 'required|in:earning,deduction',
            'status' => 'required|boolean',
            'calculation_type' => 'nullable|string',
            'calculation_value' => 'nullable|numeric',
            'is_residual' => 'nullable|boolean',
            'location_id' => 'required|array'
        ]);

        if ($request->has('is_residual') && $request->is_residual) {
            // Reset other residuals if this one is set
            SalaryComponent::where('id', '!=', $request->id)
                ->where('is_residual', true)
                ->update(['is_residual' => false]);
            $validated['calculation_type'] = 'residual';
        }

        // if calculation type is residual, ensure flag is set
        if (isset($validated['calculation_type']) && $validated['calculation_type'] === 'residual') {
             $validated['is_residual'] = true;
             // Reset others
             SalaryComponent::where('id', '!=', $request->id)->update(['is_residual' => false]);
        }

        $component->update($validated);

        // Log the activity
        ActivityLogService::log(
            'salary_component_update',
            'Updated salary component',
            "Updated salary component: {$component->name} ({$component->short_name})",
            [
                'component_id' => $component->id,
                'old_data' => $oldData,
                'new_data' => $component->fresh()->toArray()
            ]
        );

        return redirect()->route('form/salary-component/manage')->with('success', 'Component updated.');
    }

    // Delete Component
    public function destroy(SalaryComponent $salaryComponent)
    {
        // Store component data for logging before deletion
        $componentData = [
            'id' => $salaryComponent->id,
            'name' => $salaryComponent->name,
            'short_name' => $salaryComponent->short_name,
            'type' => $salaryComponent->type,
            'status' => $salaryComponent->status
        ];

        $salaryComponent->delete();

        // Log the activity
        ActivityLogService::log(
            'salary_component_delete',
            'Deleted salary component',
            "Deleted salary component: {$componentData['name']} ({$componentData['short_name']})",
            [
                'deleted_component' => $componentData
            ]
        );

        return redirect()->route('form/salary-component/manage')
            ->with('success', 'Component deleted successfully');
    }
}
