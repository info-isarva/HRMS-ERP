<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionsApiController extends Controller
{
    /**
     * Fetch all permissions.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::select('id', 'display_name', 'name', 'route_names', 'module', 'action', 'description', 'is_active')
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $formattedPermissions = $permissions->map(function ($group, $module) {
            return [
                'module' => $module,
                'permissions' => $group
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $formattedPermissions,
        ]);
    }
}