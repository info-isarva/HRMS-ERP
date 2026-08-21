<?php

namespace App\Http\Controllers;

use App\Models\PoshComplaint;
use Illuminate\Http\Request;

class ManagementController extends Controller
{
    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $pending = PoshComplaint::where('organization_id', $orgId)
            ->where('status', 'Management Action Pending (60 days)')
            ->orderByDesc('updated_at')
            ->get();

        $recentRecs = PoshComplaint::where('organization_id', $orgId)
            ->where('status', 'Recommendation Pending')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('management.index', compact('pending', 'recentRecs'));
    }
}
