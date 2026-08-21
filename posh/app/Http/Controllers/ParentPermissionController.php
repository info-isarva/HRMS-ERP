<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\ParentPermission;

class ParentPermissionController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:parent_permissions,name',
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first('name'),
            ], 422);
        }

        $parent = ParentPermission::create([
            'name' => $request->name,
            'created_by' => auth()->id(),
            'created_at' => now(),
                
        ]);

        return response()->json([
            'success' => true,
            'parent' => $parent,
        ]);
    }

    public function autocomplete(Request $request)
    {
        $query = $request->input('q');
        $results = [];
        if ($query) {
            $parents = \App\Models\ParentPermission::where('name', 'like', "%{$query}%")
                ->orderBy('name')
                ->limit(10)
                ->get();
        } else {
            $parents = \App\Models\ParentPermission::orderBy('name')->limit(10)->get();
        }
        foreach ($parents as $parent) {
            $results[] = [
                'id' => $parent->id,
                'text' => $parent->name
            ];
        }
        return response()->json(['results' => $results]);
    }
}
