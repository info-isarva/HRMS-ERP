<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use PDF;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    
    /** Main Dashboard */
    public function index()
    {
        // Auto-process approved exits whose last working day has passed
        try {
            app(\App\Services\ExitProcessorService::class)->processApprovedExits();
        } catch (\Exception $e) {
            \Log::error('Error auto-processing exits: ' . $e->getMessage());
        }

        // Get dashboard data from DashboardController
        $dashboardController = new \App\Http\Controllers\DashboardController();
        $result = $dashboardController->index();
        
        // If the result is a view, extract the data and return the same view with data
        if ($result instanceof \Illuminate\View\View) {
            return $result;
        }
        
        // If result contains data array, pass it to the dashboard view
        return view('dashboard.dashboard', $result->getData());
    }
    
    /** Employee Dashboard */
    public function emDashboard()
    {
        $dt        = Carbon::now();
        $todayDate = $dt->toDayDateTimeString();
        return view('dashboard.emdashboard',compact('todayDate'));
    }

    /** Generate PDF */
    public function generatePDF(Request $request)
    {
        // $data = ['title' => 'Welcome to ItSolutionStuff.com'];
        // $pdf = PDF::loadView('payroll.salaryview', $data);
        // return $pdf->download('text.pdf');
        // selecting PDF view
        $pdf = PDF::loadView('payroll.salaryview');
        // download pdf file
        return $pdf->download('pdfview.pdf');
    }
}
