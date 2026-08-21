<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\UserWelcomeMail;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Admin: Update 2FA status for any user
     */
    public function update2FA(Request $request, $id)
    {
        if (!auth()->user()->hasCrmPermission('edit_crm_user_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $user = User::findOrFail($id);
        $enabled = $request->has('2fa_enabled');
        $user->{"2fa_enabled"} = $enabled;
        $user->save();
        // If the admin updated their own 2FA setting, set/clear the session flag
        // so the current session isn't immediately forced into the 2FA challenge.
        if (auth()->check() && auth()->id() == $user->id) {
            if ($enabled) {
                $request->session()->put('2fa:passed', true);
            } else {
                $request->session()->forget('2fa:passed');
            }
        }

        return redirect()->route('users.index')->with('success', '2FA status updated for user.');
    }
    public function index()
    {
        if (!auth()->user()->hasCrmPermission('manage_crm_user_guard')) {
            abort(403, 'Unauthorized action.');
        }
        // Only allow super admin to view user list
        $currentUser = auth()->user();
        // if (strtolower($currentUser->crm_role_type) !== 'super admin' && strtolower($currentUser->crm_role_type) !== 'super_admin') {
        //     abort(403, 'Unauthorized action.');
        // }
        if ($currentUser->crm_role_type === 0) { // Super Admin or Admin
            $users = User::orderBy('name')->paginate(10);
        }
        elseif( $currentUser->crm_role_type === 1) { // Admin
            $users = User::whereIn('crm_role_type', [1, 2,3])->orderBy('name')->paginate(10);
        }elseif ($currentUser->crm_role_type === 2) { // Manager
            $users = User::where('assign_manager', $currentUser->id)->orderBy('name')->paginate(10);
        } else {
            $users = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10); // Empty paginator for unauthorized roles
        }
        // Fetch all roles as [id => name]
    $roles = \App\Models\Role::pluck('name', 'id');
    $allPermissions = \App\Models\Permission::orderBy('name')->pluck('name')->toArray();
    return view('users.index', compact('users', 'roles', 'allPermissions'));
    }

    public function create()
    {
        if (!auth()->user()->hasCrmPermission('create_crm_user_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $crmRoles = \App\Models\Role::orderBy('name')->get();
        $permissions = Permission::with('parentPermission')->orderBy('name')->get();
        $rolePermissionsMap = [];
        foreach ($crmRoles as $role) {
            $rolePermissionsMap[$role->id] = $role->permissions()->pluck('guard_name')->toArray();
        }
        $selectedRoleId = old('crm_role_type', $crmRoles[0]->id ?? null); // Always use role ID
        $defaultPermissions = isset($rolePermissionsMap[$selectedRoleId]) ? $rolePermissionsMap[$selectedRoleId] : [];
        $groupedPermissions = $permissions->groupBy(function($item) {
            return $item->parentPermission ? $item->parentPermission->name : 'No Parent';
        });
        // Fetch managers for the dropdown
        $managers = User::where('crm_role_type', '2')->get();

        return view('users.create', compact('crmRoles', 'permissions', 'rolePermissionsMap', 'defaultPermissions', 'groupedPermissions', 'managers'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('create_crm_user_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z][A-Za-z .\'-]*$/',
            ],
            'sales_target' => ['nullable','numeric','min:0'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',
            ],
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required_with:password|same:password',
            'crm_role_type' => 'required|string',
            //'assign_manager' => 'required_if:crm_role_type,3|exists:users,id',
            'assign_manager' => [
                 Rule::requiredIf($request->crm_role_type == 3),
                'nullable',
                'exists:users,id'
            ],
            'crm_page_right' => 'array',
            'status' => 'integer|in:0,1',
        ], [
            'name.regex' => 'Name must start with a letter and may only contain letters, spaces, dots, apostrophes, and hyphens.',
            'email.regex' => 'Please enter a valid email address.',
            'assign_manager' => 'Assign Manager is required when the role is Employee.',
        ]);
        $plainPassword = $validated['password'];
        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = $plainPassword;
        $user->crm_role_type = $validated['crm_role_type'];
        $user->sales_target = $validated['sales_target'] ?? 0;
        $user->crm_page_right = json_encode($validated['crm_page_right'] ?? []);
        $user->assign_manager = $validated['assign_manager'] ?? null;
        $user->status = $validated['status'] ?? 1;
        $user->created_by = auth()->id();
        $user->save();


        // Generate email verification URL (absolute)
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        if (!str_starts_with($verificationUrl, config('app.url'))) {
            $verificationUrl = config('app.url') . $verificationUrl;
        }

        // Send welcome email with verification link, username, and password
        Mail::to($user->email)->send(new UserWelcomeMail($user, $plainPassword, $verificationUrl));

        return redirect()->route('users.index')->with('success', 'User created successfully! Verification email sent.');
    }

    public function edit($id)
    {
        if (!auth()->user()->hasCrmPermission('edit_crm_user_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $user = User::findOrFail($id);
        $crmRoles = \App\Models\Role::orderBy('name')->get();
        $permissions = Permission::with('parentPermission')->orderBy('name')->get();
        $rolePermissionsMap = [];
        foreach ($crmRoles as $role) {
            $rolePermissionsMap[$role->id] = $role->permissions()->pluck('guard_name')->toArray();
        }
        $selectedRoleId = old('crm_role_type', $user->crm_role_type); // Always use role ID
        $defaultPermissions = isset($rolePermissionsMap[$selectedRoleId]) ? $rolePermissionsMap[$selectedRoleId] : [];
        $groupedPermissions = $permissions->groupBy(function($item) {
            return $item->parentPermission ? $item->parentPermission->name : 'No Parent';
        });
        // Fetch managers for the dropdown
        $managers = User::where('crm_role_type', 2)->get();

        return view('users.edit', compact('user', 'crmRoles', 'permissions', 'rolePermissionsMap', 'defaultPermissions', 'groupedPermissions', 'managers'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasCrmPermission('edit_crm_user_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z][A-Za-z .\'-]*$/',
            ],
            'sales_target' => ['nullable','numeric','min:0'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'crm_role_type' => 'required|string',
            'crm_page_right' => 'array',
            'assign_manager' => [
                 Rule::requiredIf($request->crm_role_type == 3),
                'nullable',
                'exists:users,id'
            ],
            'status' => 'integer|in:0,1',
        ], [
            'name.regex' => 'Name must start with a letter and may only contain letters, spaces, dots, apostrophes, and hyphens.',
            'email.regex' => 'Please enter a valid email address.',
            'assign_manager' => 'Assign Manager is required when the role is Employee.',
        ]);
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }
        $user->sales_target = $validated['sales_target'] ?? ($user->sales_target ?? 0);
        $user->crm_role_type = $validated['crm_role_type'];
        $user->crm_page_right = json_encode($validated['crm_page_right'] ?? []);
        if ($validated['crm_role_type'] == 3) {
            $user->assign_manager = $validated['assign_manager'] ?? $user->assign_manager;
        } else {
            $user->assign_manager = null;
        }
        $user->status = $validated['status'] ?? $user->status;
        $user->updated_by = auth()->id();
        $user->save();
        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasCrmPermission('delete_crm_user_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}
