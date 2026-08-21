<?php

namespace App\Http\Controllers;

/**
 * @deprecated Phase 0 — Legacy employee POSH portal. Replaced by ISARVA POSH module.
 */
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PoshComplianceController extends Controller
{
    private function getApiUrl()
    {
        return env('PAYROLL_SYNC_URL', 'https://payrolldev.isarva.in');
    }

    private function getSyncToken()
    {
        return env('PAYROLL_SYNC_TOKEN', 'default-token');
    }

    /**
     * Display the employee's POSH compliance dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $apiUrl = $this->getApiUrl();
        $syncToken = $this->getSyncToken();

        $iccMembers = [];
        $complaints = [];

        // Fetch ICC Members
        try {
            $response = Http::timeout(10)->get($apiUrl . '/api/compliance/posh/icc-board', [
                'sync_token' => $syncToken
            ]);
            
            if ($response->successful()) {
                $iccMembers = $response->json()['members'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch ICC Board members from Payroll', ['error' => $e->getMessage()]);
        }

        // Fetch Employee's filed complaints
        try {
            $response = Http::timeout(10)->get($apiUrl . '/api/compliance/posh/complaints', [
                'email' => $user->email,
                'sync_token' => $syncToken
            ]);

            if ($response->successful()) {
                $complaints = $response->json()['complaints'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch employee POSH complaints from Payroll', ['error' => $e->getMessage()]);
        }

        return view('compliance.posh.index', compact('iccMembers', 'complaints'));
    }

    /**
     * Store a new POSH complaint
     */
    public function storeComplaint(Request $request)
    {
        $request->merge([
            'is_anonymous' => $request->has('is_anonymous') ? true : false,
        ]);

        $request->validate([
            'is_anonymous' => 'required|boolean',
            'incident_date' => 'required|date',
            'incident_location' => 'required|string',
            'respondent_name' => 'required|string',
            'respondent_department' => 'nullable|string',
            'description' => 'required|string',
        ]);

        $user = Auth::user();
        $apiUrl = $this->getApiUrl();
        $syncToken = $this->getSyncToken();

        try {
            $response = Http::timeout(10)->post($apiUrl . '/api/compliance/posh/complaint', [
                'email' => $user->email,
                'is_anonymous' => $request->is_anonymous,
                'incident_date' => $request->incident_date,
                'incident_location' => $request->incident_location,
                'respondent_name' => $request->respondent_name,
                'respondent_department' => $request->respondent_department,
                'description' => $request->description,
                'sync_token' => $syncToken
            ]);

            if ($response->successful()) {
                return redirect()->back()->with('success', 'Your complaint has been submitted confidentially to the ICC Board.');
            } else {
                Log::error('Payroll API rejected POSH complaint', ['response' => $response->body()]);
                return redirect()->back()->with('error', 'Failed to submit complaint. Please contact administrator.');
            }
        } catch (\Exception $e) {
            Log::error('Error submitting POSH complaint to Payroll', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Communication error with master database. Please try again later.');
        }
    }

    /**
     * Display a specific complaint's details and logs
     */
    public function showComplaint($id)
    {
        $user = Auth::user();
        $apiUrl = $this->getApiUrl();
        $syncToken = $this->getSyncToken();

        try {
            $response = Http::timeout(10)->get($apiUrl . '/api/compliance/posh/complaint/' . $id, [
                'email' => $user->email,
                'sync_token' => $syncToken
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'complaint' => $data['complaint'],
                    'logs' => $data['logs']
                ]);
            } else {
                return response()->json(['success' => false, 'message' => 'Unauthorized or case not found.'], 403);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching POSH complaint details', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error communicating with server.'], 500);
        }
    }
}
