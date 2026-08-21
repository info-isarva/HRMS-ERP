<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // Index - Manage Roles
    public function index()
    {
        $roles = Role::latest()->get();
        return view('masters.roles.index', compact('roles'));
    }    

    // Store Role
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name' => 'required|string|max:255|unique:roles',
            'short_name' => 'nullable|string|max:50|unique:roles',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ], [
            'role_name.unique' => 'A role with this name already exists.',
            'short_name.unique' => 'A role with this short name already exists.',
        ]);

        Role::create($validated);

        return redirect()->route('form/role/manage')
            ->with('success', 'Role created successfully');
    }

    
    public function getById($id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    // Update Role
    public function update(Request $request)
    {
        $role = Role::findOrFail($request->id);
        
        $validated = $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,role_name,' . $role->id,
            'short_name' => 'nullable|string|max:50|unique:roles,short_name,' . $role->id,
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ], [
            'role_name.unique' => 'A role with this name already exists.',
            'short_name.unique' => 'A role with this short name already exists.',
        ]);

        $role->update($validated);

        return redirect()->route('form/role/manage')->with('success', 'Role updated successfully.');
    }

    // Delete Role
    public function destroy(Request $request)
    {
        $role = Role::findOrFail($request->id);
        $role->delete();
        return redirect()->route('form/role/manage')
            ->with('success', 'Role deleted successfully');
    }
}
