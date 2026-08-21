<?php

namespace App\Http\Controllers;

use App\Services\PayrollApiService;
use Illuminate\Http\Request;

class Form16Controller extends Controller
{
    public function __construct(private PayrollApiService $payrollApi)
    {
    }

    public function index(Request $request)
    {
        $yearsList = $this->payrollApi->getForm16List() ?? [];

        return view('form16.index', [
            'yearsList' => $yearsList,
        ]);
    }

    public function download(Request $request, string $year)
    {
        if (!preg_match('/^\d{4}-\d{4}$/', $year)) {
            abort(400, 'Invalid year format');
        }

        $response = $this->payrollApi->downloadForm16Pdf($year);

        if (! $response) {
            return redirect()
                ->route('form16.index')
                ->with('error', 'Form 16 is not available for the selected financial year.');
        }

        $filename = sprintf('Form_16_%s.pdf', $year);

        return response($response->body(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
