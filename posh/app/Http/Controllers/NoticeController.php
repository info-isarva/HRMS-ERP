<?php

namespace App\Http\Controllers;

use App\Models\PoshComplaint;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function respondentNotice(Request $request, PoshComplaint $complaint)
    {
        abort_unless($complaint->organization_id === $request->user()->organization_id, 403);
        $this->authorizeIc($request);

        return view('cases.notice-print', compact('complaint'));
    }

    protected function authorizeIc(Request $request): void
    {
        abort_unless(in_array($request->user()->posh_role, config('posh.ic_roles_access'), true), 403);
    }
}
