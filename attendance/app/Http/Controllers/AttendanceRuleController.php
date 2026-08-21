<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRule;
use Illuminate\Http\Request;

class AttendanceRuleController extends Controller
{
    public function index()
    {
        $rules = AttendanceRule::all();
        return view('attendance-rules.index', compact('rules'));
    }

    public function create()
    {
        return view('attendance-rules.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'shift_threshold_hours' => 'required|numeric|min:0',
            'recovery_days_offset' => 'required|integer|min:0',
            'recovery_status' => 'required|string|in:present,absent,compoff',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        AttendanceRule::create($data);

        return redirect()->route('attendance-rules.index')->with('success', 'Rule created successfully.');
    }

    public function edit(AttendanceRule $attendanceRule)
    {
        return view('attendance-rules.edit', ['rule' => $attendanceRule]);
    }

    public function update(Request $request, AttendanceRule $attendanceRule)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'shift_threshold_hours' => 'required|numeric|min:0',
            'recovery_days_offset' => 'required|integer|min:0',
            'recovery_status' => 'required|string|in:present,absent,compoff',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $attendanceRule->update($data);

        return redirect()->route('attendance-rules.index')->with('success', 'Rule updated successfully.');
    }

    public function destroy(AttendanceRule $attendanceRule)
    {
        $attendanceRule->delete();
        return redirect()->route('attendance-rules.index')->with('success', 'Rule deleted successfully.');
    }
}
