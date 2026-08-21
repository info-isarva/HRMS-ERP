<?php

namespace App\Http\Controllers;

use App\Models\RolesPermissions;
use App\Models\CompanySettings;
use Illuminate\Http\Request;
use DB;

class SettingController extends Controller
{
    /** Company Settings Page */
    public function companySettings()
    {
        $companySettings = CompanySettings::where('id',1)->first();
        return view('settings.companysettings',compact('companySettings'));
    }

    /** Save Record Company Settings */
    public function saveRecord(Request $request)
    {
        // validate form
        $request->validate([
            'company_name'   =>'required',
            'contact_person' =>'required',
            'address'        =>'required',
            'country'        =>'required',
            'city'           =>'required',
            'state_province' =>'required',
            'postal_code'    =>'',
            'email'          =>'required|email',
            'phone_number'   =>'',
            'mobile_number'  =>'required',
            'fax'            =>'',
            'website_url'    =>'',
            'company_pan'    =>'nullable|string|max:50',
            'company_tan'    =>'nullable|string|max:50',
            'logo_image' => 'nullable|image|max:2048', // Add validation for profile image
            'favicon_image' => 'nullable|image|max:2048', // Add validation for profile image
        ]);

        try {

            // save or update to databases CompanySettings table
            $saveRecord = CompanySettings::updateOrCreate(['id' => $request->id]);
            $saveRecord->company_name   = $request->company_name;

            // Handle profile image upload if present
            if ($request->hasFile('logo_image')) {
                $logoImage = $request->file('logo_image');
                
                // Create directory if missing
                $directory = 'assets/company_image';
                if (!file_exists(public_path($directory))) {
                    mkdir(public_path($directory), 0755, true);
                }
                
                // Generate file name based on requirements: employee_id.profile_image.timestamp.extension
                $fileName = 'logo_image.' . time() . '.' . $logoImage->getClientOriginalExtension();
                
                // Move file to directory
                $logoImage->move(public_path($directory), $fileName);
                
                // Store the path in database
                $save_logo_image = $directory . '/' . $fileName;

                $saveRecord->logo_image = $save_logo_image;
            }
            
             // Handle profile image upload if present
             if ($request->hasFile('favicon_image')) {
                $logoImage = $request->file('favicon_image');
                
                // Create directory if missing
                $directory = 'assets/company_image';
                if (!file_exists(public_path($directory))) {
                    mkdir(public_path($directory), 0755, true);
                }
                
                // Generate file name based on requirements: employee_id.profile_image.timestamp.extension
                $fileName = 'favicon_image' . time() . '.' . $logoImage->getClientOriginalExtension();
                
                // Move file to directory
                $logoImage->move(public_path($directory), $fileName);
                
                // Store the path in database
                $save_favicon = $directory . '/' . $fileName;

                $saveRecord->favicon = $save_favicon;
            }
            
            

            $saveRecord->contact_person = $request->contact_person;
            $saveRecord->address        = $request->address;
            $saveRecord->country        = $request->country;
            $saveRecord->city           = $request->city;
            $saveRecord->state_province = $request->state_province;
            $saveRecord->postal_code    = $request->postal_code;
            $saveRecord->email          = $request->email;
            $saveRecord->phone_number   = $request->phone_number;
            $saveRecord->mobile_number  = $request->mobile_number;
            $saveRecord->fax            = $request->fax;
            $saveRecord->website_url    = $request->website_url;
            $saveRecord->company_pan    = $request->company_pan;
            $saveRecord->company_tan    = $request->company_tan;
            
            $saveRecord->save();
            
            DB::commit();
            flash()->success('Save CompanySettings successfully :)');
            return redirect()->back();
        } catch(\Exception $e) {
            \Log::info($e);
            DB::rollback();
            flash()->error('Save CompanySettings fail :)');
            return redirect()->back();
        }
    }

    
    
    /** Roles & Permissions  */
    public function rolesPermissions()
    {
        $rolesPermissions = RolesPermissions::All();
        return view('settings.rolespermissions',compact('rolesPermissions'));
    }

    /** Add Role Permissions */
    public function addRecord(Request $request)
    {
        $request->validate([
            'roleName' => 'required|string|max:255',
        ]);
        
        DB::beginTransaction();
        try {
            $roles = RolesPermissions::where('permissions_name', '=', $request->roleName)->first();
            if ($roles === null) {
                // roles name doesn't exist
                $role = new RolesPermissions;
                $role->permissions_name = $request->roleName;
                $role->save();
            } else {
                // roles name exits
                DB::rollback();
                flash()->error('Roles name exits :)');
                return redirect()->back();
            }

            DB::commit();
            flash()->success('Create new role successfully :)');
            return redirect()->back();
        } catch(\Exception $e) {
            DB::rollback();
            flash()->error('Logout successfully :)');
            return redirect()->back();
        }
    }

    /** Edit Roles Permissions */
    public function editRolesPermissions(Request $request)
    {
        DB::beginTransaction();
        try{
            $id        = $request->id;
            $roleName  = $request->roleName;
            
            $update = [
                'id'               => $id,
                'permissions_name' => $roleName,
            ];

            RolesPermissions::where('id',$id)->update($update);
            DB::commit();
            flash()->success('Role Name updated successfully :)');
            return redirect()->back();
        } catch(\Exception $e) {
            DB::rollback();
            flash()->error('Role Name update fail :)');
            return redirect()->back();
        }
    }

    /** Delete Roles Permissions */
    public function deleteRolesPermissions(Request $request)
    {
        try {
            RolesPermissions::destroy($request->id);
            flash()->success('Role Name deleted successfully :)');
            return redirect()->back();
        } catch(\Exception $e) {
            DB::rollback();
            flash()->error('Role Name delete fail :)');
            return redirect()->back();
        }
    }

    /** Localization */
    public function localizationIndex()
    {
        $activeCurrency = \App\Models\Setting::getValue('active_currency', 'INR');
        $currencies = \App\Helper\CurrencyHelper::getCurrencies();
        return view('settings.localization', compact('activeCurrency', 'currencies'));
    }

    /** Save Localization Settings */
    public function saveLocalizationSettings(Request $request)
    {
        try {
            if ($request->has('active_currency')) {
                \App\Models\Setting::setValue('active_currency', $request->active_currency);
            }
            
            flash()->success('Localization Settings saved successfully');
            return redirect()->back();
        } catch(\Exception $e) {
            \Log::error($e);
            flash()->error('Save Localization Settings failed: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /** Salary Settings Index */
    public function salarySettingsIndex()
    {
        $settings = \App\Models\Setting::where('group', 'salary')->pluck('value', 'key');
        // Fetch earning components for dynamic configuration
        $salaryComponents = \App\Models\SalaryComponent::where('type', 'earning')
            ->where('status', 1) // Only active components
            ->orderBy('id', 'asc')
            ->get();
            
        return view('settings.salary-settings', compact('settings', 'salaryComponents'));
    }

    /** Save Salary Settings */
    public function saveSalarySettings(Request $request)
    {
        try {
            // 1. Save general settings (PF, ESI, etc. from other cards)
            $generalData = $request->except(['_token', 'components']);
            
            foreach ($generalData as $key => $value) {
                // Ensure key starts with salary_ if not present in request
                $dbKey = str_starts_with($key, 'salary_') ? $key : 'salary_' . $key;
                
                $isValArray = is_array($value);
                \App\Models\Setting::updateOrCreate(
                    ['key' => $dbKey],
                    [
                        'display_name' => ucwords(str_replace(['_', 'salary'], [' ', ''], $dbKey)), // Auto-generate name
                        'value' => $isValArray ? json_encode($value) : $value,
                        'group' => 'salary',
                        'type' => $isValArray ? 'json' : 'text'
                    ]
                );
            }

            // 2. Save Dynamic Component Rules
            if ($request->has('components')) {
                foreach ($request->components as $compId => $compData) {
                    $component = \App\Models\SalaryComponent::find($compId);
                    if ($component) {
                        $component->calculation_type = $compData['calculation_type'] ?? 'flat_amount';
                        $component->calculation_value = $compData['calculation_value'] ?? 0;
                        
                        // Handle 'is_residual' logic (only update if it's residual type selected)
                        // Or if calculation_type is 'residual', set is_residual=true, else false
                        if(($compData['calculation_type'] ?? '') === 'residual') {
                            $component->is_residual = 1;
                            // Reset value for residual usually 0 or ignored, but kept for safety
                        } else {
                            $component->is_residual = 0;
                        }
                        
                        $component->save();
                    }
                }
            }

            flash()->success('Salary Settings saved successfully');
            return redirect()->back();
        } catch(\Exception $e) {
            \Log::error($e);
            flash()->error('Save Salary Settings failed: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /** Email Settings */
    public function emailSettingsIndex()
    {
        return view('settings.email-settings');
    }

    /** Permission Management */
    public function permissionManagement()
    {
        $permissions = \App\Models\Permission::orderBy('module', 'asc')->orderBy('name', 'asc')->get();
        $modules = \App\Models\Permission::distinct()->pluck('module')->sort()->values();
        
        // Get all available routes for the dropdown
        $allRoutes = collect(\Route::getRoutes())
            ->map(function ($route) {
                return $route->getName();
            })
            ->filter()
            ->sort()
            ->values();
            
        // Get already used route names to exclude from dropdown
        $usedRoutes = \App\Models\Permission::getAllUsedRouteNames();
        
        // Debug log to check used routes (temporarily commented out)
        // \Log::info('Used routes count: ' . $usedRoutes->count());
        // \Log::info('Used routes: ' . json_encode($usedRoutes->toArray()));
        
        // Filter out used routes for the dropdown
        $availableRoutes = $allRoutes->diff($usedRoutes)->values()->toArray();
        
        // Debug log to check available routes (temporarily commented out)
        // \Log::info('Available routes count: ' . count($availableRoutes));
        // \Log::info('Available employee routes: ' . json_encode(array_filter($availableRoutes, function($route) {
        //     return str_contains($route, 'employees');
        // })));
        
        // Get route suggestions for common patterns
        $routeSuggestions = $this->getRouteSuggestions($allRoutes);
            
        return view('settings.permissions.index', compact('permissions', 'modules', 'availableRoutes', 'routeSuggestions'));
    }

    /** Save New Permission */
    public function savePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'route_names' => 'required|array|min:1',
            'route_names.*' => 'required|string|max:255',
            'module' => 'required|string|max:255',
            'action' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        // Check if any of the selected routes are already used
        foreach ($request->route_names as $routeName) {
            if (\App\Models\Permission::isRouteNameUsed($routeName)) {
                return response()->json([
                    'success' => false, 
                    'message' => "Route '{$routeName}' is already assigned to another permission."
                ]);
            }
        }

        DB::beginTransaction();
        try {
            \App\Models\Permission::create([
                'name' => $request->name,
                'route_name' => $request->route_names[0], // Store first route for backward compatibility
                'route_names' => $request->route_names,
                'module' => $request->module,
                'action' => $request->action,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'is_active' => true,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Permission created successfully']);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to create permission: ' . $e->getMessage()]);
        }
    }

    /** Update Permission */
    public function updatePermission(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:permissions,id',
            'name' => 'required|string|max:255|unique:permissions,name,' . $request->id,
            'route_names' => 'required|array|min:1',
            'route_names.*' => 'required|string|max:255',
            'module' => 'required|string|max:255',
            'action' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $permission = \App\Models\Permission::find($request->id);
            
            // Check if any of the selected routes are already used by other permissions
            foreach ($request->route_names as $routeName) {
                $existingPermission = \App\Models\Permission::where('id', '!=', $request->id)
                    ->where(function($query) use ($routeName) {
                        $query->where('route_name', $routeName)
                              ->orWhereJsonContains('route_names', $routeName);
                    })->first();
                    
                if ($existingPermission) {
                    return response()->json([
                        'success' => false, 
                        'message' => "Route '{$routeName}' is already assigned to permission '{$existingPermission->name}'."
                    ]);
                }
            }
            
            $permission->update([
                'name' => $request->name,
                'route_name' => $request->route_names[0], // Store first route for backward compatibility
                'route_names' => $request->route_names,
                'module' => $request->module,
                'action' => $request->action,
                'display_name' => $request->display_name,
                'description' => $request->description,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Permission updated successfully']);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to update permission: ' . $e->getMessage()]);
        }
    }

    /** Get Permission Details */
    public function getPermission($id)
    {
        $permission = \App\Models\Permission::find($id);
        if ($permission) {
            // Get all available routes for the dropdown (excluding routes used by other permissions)
            $allRoutes = collect(\Route::getRoutes())
                ->map(function ($route) {
                    return $route->getName();
                })
                ->filter()
                ->sort()
                ->values();
                
            // Get already used route names to exclude from dropdown (excluding current permission)
            $usedRoutes = collect();
            
            \App\Models\Permission::where('id', '!=', $id)->get()->each(function($perm) use (&$usedRoutes) {
                if ($perm->route_name) {
                    $usedRoutes->push($perm->route_name);
                }
                if ($perm->route_names && is_array($perm->route_names)) {
                    foreach ($perm->route_names as $routeName) {
                        $usedRoutes->push($routeName);
                    }
                }
            });
            
            $usedRoutes = $usedRoutes->unique()->values();
            
            // Filter out used routes for the dropdown
            $availableRoutes = $allRoutes->diff($usedRoutes)->values()->toArray();
            
            // Debug logging (temporarily commented out)
            // \Log::info("Edit Permission ID: {$id}");
            // \Log::info("Used routes by other permissions: " . $usedRoutes->count());
            // \Log::info("Available routes for edit: " . count($availableRoutes));
            
            return response()->json([
                'success' => true, 
                'permission' => $permission,
                'availableRoutes' => $availableRoutes
            ]);
        }
        return response()->json(['success' => false, 'message' => 'Permission not found']);
    }

    /** Delete Permission */
    public function deletePermission(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:permissions,id',
        ]);

        DB::beginTransaction();
        try {
            $permission = \App\Models\Permission::find($request->id);
            
            // Check if permission is being used by any users
            $usersWithPermission = \App\Models\User::whereJsonContains('permissions_json', (int)$request->id)->count();
            
            if ($usersWithPermission > 0) {
                return response()->json([
                    'success' => false, 
                    'message' => "Cannot delete permission. It is assigned to {$usersWithPermission} user(s)."
                ]);
            }

            $permission->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Permission deleted successfully']);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to delete permission: ' . $e->getMessage()]);
        }
    }

    /** Master Settings Index Page */
    public function masterSettingsIndex()
    {
        return view('settings.master-settings-old');
    }

    /** Save Master Settings */
    public function saveMasterSettings(Request $request)
    {
        // Implementation for saving master settings
        return response()->json(['success' => true, 'message' => 'Settings saved successfully']);
    }

    /**
     * Get route suggestions for common patterns (CRUD operations)
     */
    private function getRouteSuggestions($allRoutes)
    {
        $suggestions = [];
        
        // Group routes by base name patterns
        $groupedRoutes = [];
        
        foreach ($allRoutes as $route) {
            // Skip routes without dots
            if (!str_contains($route, '.')) {
                continue;
            }
            
            // Extract base pattern (e.g., 'employees' from 'employees.edit')
            $parts = explode('.', $route);
            $baseName = $parts[0];
            $action = $parts[1] ?? '';
            
            if (!isset($groupedRoutes[$baseName])) {
                $groupedRoutes[$baseName] = [];
            }
            
            $groupedRoutes[$baseName][$action] = $route;
        }
        
        // Create suggestions for common CRUD patterns
        foreach ($groupedRoutes as $baseName => $routes) {
            // Add Employee-style pattern (view, edit with new/save and edit/update)
            if (isset($routes['new']) && isset($routes['save'])) {
                $suggestions[] = [
                    'label' => ucfirst($baseName) . ' - Add/Create',
                    'routes' => [$routes['new'], $routes['save']],
                    'description' => 'Routes for creating new ' . $baseName
                ];
            }
            
            if (isset($routes['edit']) && isset($routes['update'])) {
                $suggestions[] = [
                    'label' => ucfirst($baseName) . ' - Edit/Update',
                    'routes' => [$routes['edit'], $routes['update']],
                    'description' => 'Routes for editing existing ' . $baseName
                ];
            }
            
            // Standard CRUD patterns
            if (isset($routes['create']) && isset($routes['store'])) {
                $suggestions[] = [
                    'label' => ucfirst($baseName) . ' - Create',
                    'routes' => [$routes['create'], $routes['store']],
                    'description' => 'Routes for creating new ' . $baseName
                ];
            }
            
            // View permissions
            if (isset($routes['index']) && isset($routes['show'])) {
                $suggestions[] = [
                    'label' => ucfirst($baseName) . ' - View',
                    'routes' => [$routes['index'], $routes['show']],
                    'description' => 'Routes for viewing ' . $baseName
                ];
            }
        }
        
        return $suggestions;
    }
}
