<?php

namespace App\Http\Controllers;

use App\Services\PayrollApiService;
use Illuminate\Http\Request;

class AdvanceController extends Controller
{
    public function __construct(private PayrollApiService $payrollApi)
    {
    }

    public function index(Request $request)
    {
        $advancesList = $this->payrollApi->getAdvances() ?? [];

        return view('advances.index', [
            'advancesList' => $advancesList,
        ]);
    }
}
