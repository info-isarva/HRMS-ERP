<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Api\LeaveTypeController as LegacyLeaveTypeController;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return app(LegacyLeaveTypeController::class)->index($request);
    }
}
