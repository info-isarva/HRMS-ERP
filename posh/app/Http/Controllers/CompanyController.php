<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\FinancialYear;
use App\Http\Controllers\BackupController;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CompanyController extends Controller
{
    public function edit()
    {
        $company = Company::first();
        $user = auth()->user();
        $canEdit = false;
        if ($user) {
            $role = $user->crm_role_type ?? null;
            $canEdit = ($role === 0 || $role === 1);
        }

        return view('company.edit', compact('company', 'canEdit'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $role = $user->crm_role_type ?? null;
        if (! ($role === 0 || $role === 1)) {
            abort(403, 'Unauthorized');
        }

        // Allow common image types and SVG. Note: SVG files can contain scripts — if you accept
        // user-supplied SVGs consider sanitizing them or converting to PNG server-side.
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|mimes:png,jpg,jpeg,gif,webp,svg|max:2048',
            'favicon' => 'nullable|mimes:png,ico,svg|max:512',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'fy_start_month' => 'nullable|integer|min:1|max:12',
            'fy_start_day' => 'nullable|integer|min:1|max:31',
            'fy_end_month' => 'nullable|integer|min:1|max:12',
            'fy_end_day' => 'nullable|integer|min:1|max:31',
            'currency_code' => 'nullable|string|max:10',
            'currency_symbol' => 'nullable|string|max:10',
            'currency_position' => 'nullable|in:prefix,suffix',
            'country' => 'nullable|string|max:100',
        ]);

        $company = Company::first();
        if (! $company) {
            $company = new Company();
        }

        if ($request->hasFile('logo')) {
            // delete old logo if exists (from public assets)
            if ($company->logo) {
                $oldPath = public_path('assets/company_image/' . $company->logo);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $file = $request->file('logo');
            $originalExtension = $file->getClientOriginalExtension();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $folder = 'company';
            $filename = $originalName . '_' . time() . '.' . $originalExtension;
            $relativeDir = 'assets/company_image/' . $folder;

            $destination = public_path($relativeDir);
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            $file->move($destination, $filename);
            $data['logo'] = $folder . '/' . $filename; // store path relative to assets/company_image
        }
        if ($request->hasFile('favicon')) {
            // delete old favicon if exists (from public assets)
            if ($company->favicon) {
                $oldF = public_path('assets/company_image/' . $company->favicon);
                if (file_exists($oldF)) {
                    @unlink($oldF);
                }
            }

            $file = $request->file('favicon');
            $originalExtension = $file->getClientOriginalExtension();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $folder = 'company';
            $filename = $originalName . '_' . time() . '.' . $originalExtension;
            $relativeDir = 'assets/company_image/' . $folder;

            $destination = public_path($relativeDir);
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            $file->move($destination, $filename);
            $data['favicon'] = $folder . '/' . $filename; // store path relative to assets/company_image
        }

        $company->fill($data);
        $company->save();

        return redirect()->route('company.edit')->with('success', 'Company settings updated.');
    }

    /**
     * Close current financial year: take a backup, mark current FY inactive/closed, create next FY as inactive.
     */
    public function closeFinancialYear(Request $request)
    {
        $user = auth()->user();
        $role = $user->crm_role_type ?? null;
        if (! ($role === 0 || $role === 1)) {
            abort(403, 'Unauthorized');
        }

        // Find active financial year
        $current = FinancialYear::where('active', 1)->first();

        // Prevent closing before the fiscal year end
        if ($current) {
            $today = Carbon::today();
            if (! $today->gt(Carbon::parse($current->to_date))) {
                return redirect()->route('company.edit')->with('error', 'Financial year cannot be closed before its end date: ' . Carbon::parse($current->to_date)->toDateString());
            }
        }

        // Create a backup before making changes
        try {
            $backupCtrl = new BackupController();
            $gzPath = $backupCtrl->createStoredBackup();
        } catch (\Exception $e) {
            return redirect()->route('company.edit')->with('error', 'Failed to create backup before closing financial year: ' . $e->getMessage());
        }

        // Close current FY if exists
        if ($current) {
            $current->status = 'closed';
            $current->active = 0;
            $current->save();
        }

        // Create next financial year entry (inactive by default)
        if ($current) {
            $nextFrom = $current->to_date->addDay();
            $nextTo = (clone $nextFrom)->addYear()->subDay();
            $finKey = $nextTo->format('y');
            FinancialYear::create([
                'from_date' => $nextFrom->toDateString(),
                'to_date' => $nextTo->toDateString(),
                'fin_key' => $finKey,
                'status' => 'running',
                'active' => 1,
            ]);
        }

        return redirect()->route('company.edit')->with('success', 'Financial year closed and next financial year created. Backup stored at: ' . $gzPath);
    }

    // Show the close financial year page
    public function showCloseYearPage()
    {
        $user = auth()->user();
        $role = $user->crm_role_type ?? null;
        if (! ($role === 0 || $role === 1)) {
            abort(403, 'Unauthorized');
        }

        $current = FinancialYear::where('active', 1)->first();
        $canClose = false;
        if ($current) {
            $today = Carbon::today();
            $canClose = $today->gt(Carbon::parse($current->to_date));
        }

        return view('finance.close_year', compact('current', 'canClose'));
    }

    // AJAX: perform backup and return JSON with stored path
    public function ajaxBackup(Request $request)
    {
        $user = auth()->user();
        $role = $user->crm_role_type ?? null;
        if (! ($role === 0 || $role === 1)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        // Ensure closing is allowed (don't allow backup-only if closing is forbidden)
        $current = FinancialYear::where('active', 1)->first();
        if ($current) {
            $today = Carbon::today();
            if (! $today->gt(Carbon::parse($current->to_date))) {
                return response()->json(['success' => false, 'error' => 'Cannot close financial year before its end date.'], 400);
            }
        }

        try {
            $backupCtrl = new BackupController();
            $gzPath = $backupCtrl->createStoredBackup();
            return response()->json(['success' => true, 'file' => basename($gzPath)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // AJAX: create next financial year (assumes backup already done)
    public function ajaxCreateNextYear(Request $request)
    {
        $user = auth()->user();
        $role = $user->crm_role_type ?? null;
        if (! ($role === 0 || $role === 1)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $current = FinancialYear::where('active', 1)->first();
        if (! $current) {
            return response()->json(['success' => false, 'error' => 'No active financial year found']);
        }

        $today = Carbon::today();
        if (! $today->gt(Carbon::parse($current->to_date))) {
            return response()->json(['success' => false, 'error' => 'Cannot close financial year before its end date.']);
        }

        $current->status = 'closed';
        $current->active = 0;
        $current->save();

        $nextFrom = Carbon::parse($current->to_date)->addDay();
        $nextTo = (clone $nextFrom)->addYear()->subDay();
        $finKey = $nextTo->format('y');
        $next = FinancialYear::create([
            'from_date' => $nextFrom->toDateString(),
            'to_date' => $nextTo->toDateString(),
            'fin_key' => $finKey,
            'status' => 'running',
            'active' => 1,
        ]);

        return response()->json(['success' => true, 'fin_key' => $next->fin_key]);
    }

    // Store selected financial year ID in session
    public function selectFinancialYear(Request $request)
    {
        $fyId = $request->input('financial_year_id');
        if (! $fyId) {
            return response()->json(['success' => false, 'error' => 'Missing financial_year_id'], 400);
        }
        $fy = FinancialYear::find($fyId);
        if (! $fy) {
            return response()->json(['success' => false, 'error' => 'Financial year not found'], 404);
        }
        session(['selected_financial_year' => $fy->id]);
        return response()->json(['success' => true, 'financial_year' => $fy->fin_key]);
    }
}
