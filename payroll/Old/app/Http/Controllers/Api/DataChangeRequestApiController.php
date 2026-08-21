<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataChangeRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\UserConsent;

class DataChangeRequestApiController extends Controller
{
    private function validateToken(Request $request)
    {
        $expectedToken = env('ATTENDANCE_SYNC_TOKEN', env('JWT_HMAC_SECRET'));
        if ($request->sync_token !== $expectedToken && $request->bearerToken() !== $expectedToken) {
            return false;
        }
        return true;
    }

    public function store(Request $request)
    {
        if (!$this->validateToken($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'user_email' => 'required|email',
            'request_type' => 'required|string',
            'details' => 'required|string',
            'source_system' => 'required|string',
        ]);

        try {
            $dataRequest = DataChangeRequest::create([
                'user_email' => $request->user_email,
                'request_type' => $request->request_type,
                'details' => $request->details,
                'source_system' => $request->source_system,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request submitted successfully.',
                'data' => $dataRequest
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create data change request via API', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create request'], 500);
        }
    }

    public function index(Request $request)
    {
        if (!$this->validateToken($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'user_email' => 'required|email'
        ]);

        $requests = DataChangeRequest::where('user_email', $request->user_email)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $consent = UserConsent::where('user_id', function ($query) use ($request) {
            $query->select('id')->from('users')->where('email', $request->user_email)->limit(1);
        })->where('policy_type', 'dpdp_act')->first();

        return response()->json([
            'success' => true,
            'requests' => $requests,
            'consent' => $consent
        ]);
    }
}
