<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataChangeRequest;
use Illuminate\Support\Facades\Auth;

class DataChangeRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = DataChangeRequest::with('employee')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_email', 'like', '%' . $search . '%')
                  ->orWhere('request_type', 'like', '%' . $search . '%')
                  ->orWhere('details', 'like', '%' . $search . '%')
                  ->orWhereHas('employee', function ($subQuery) use ($search) {
                      $subQuery->where('name', 'like', '%' . $search . '%')
                               ->orWhere('employee_id', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source_system')) {
            $query->where('source_system', $request->source_system);
        }

        $requests = $query->get();
        $filters = $request->all();

        return view('compliance.data_requests', compact('requests', 'filters'));
    }

    public function resolve(Request $request, $id)
    {
        $dataRequest = DataChangeRequest::findOrFail($id);
        
        $dataRequest->status = 'resolved';
        $dataRequest->resolved_at = now();
        $dataRequest->resolved_by = Auth::id();
        $dataRequest->save();
        
        flash()->success('Request marked as resolved successfully.');
        return redirect()->back();
    }
}
