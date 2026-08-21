<?php

namespace App\Http\Controllers;

use App\Models\PoshAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = PoshAuditLog::where('organization_id', Auth::user()->organization_id)
            ->with('user')
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('action', 'like', "%{$q}%")
                    ->orWhere('case_number', 'like', "%{$q}%")
                    ->orWhere('details', 'like', "%{$q}%");
            });
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('audit.index', compact('logs'));
    }
}
