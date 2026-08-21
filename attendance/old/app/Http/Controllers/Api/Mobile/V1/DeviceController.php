<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Models\MobileDeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    /**
     * Register or refresh an FCM device token for the logged-in user.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string|max:512',
            'platform' => 'nullable|string|in:android,ios',
            'device_id' => 'nullable|string|max:191',
        ]);

        $user = Auth::guard('api')->user();

        $device = MobileDeviceToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'fcm_token' => $validated['fcm_token'],
            ],
            [
                'platform' => $validated['platform'] ?? null,
                'device_id' => $validated['device_id'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device registered for push notifications.',
            'data' => [
                'id' => $device->id,
                'platform' => $device->platform,
                'device_id' => $device->device_id,
                'registered_at' => $device->updated_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Remove an FCM token (e.g. on logout from app).
     */
    public function unregister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string|max:512',
        ]);

        $user = Auth::guard('api')->user();

        $deleted = MobileDeviceToken::query()
            ->where('user_id', $user->id)
            ->where('fcm_token', $validated['fcm_token'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted ? 'Device unregistered.' : 'Device token was not found.',
            'data' => ['removed' => (bool) $deleted],
        ]);
    }

    /**
     * List current user's registered devices (for debugging / settings UI).
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $devices = MobileDeviceToken::query()
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->get(['id', 'platform', 'device_id', 'created_at', 'updated_at'])
            ->map(fn ($d) => [
                'id' => $d->id,
                'platform' => $d->platform,
                'device_id' => $d->device_id,
                'registered_at' => $d->updated_at?->toISOString(),
            ]);

        return response()->json([
            'success' => true,
            'data' => ['devices' => $devices],
        ]);
    }
}
