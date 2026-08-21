<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DealFileController extends Controller
{
    public function __construct()
    {
        // Prevent creating/editing/deleting deal files when a historical financial year is selected.
        // Use the middleware class directly to avoid alias resolution issues.
        $this->middleware(\App\Http\Middleware\PreventHistoricalFinancialYear::class)->only([
            'store', 'destroy', 'download'
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'file_type' => 'required|in:file upload,file links',
            'file_upload' => 'nullable|file|mimetypes:application/pdf,image/jpeg,image/png,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/octet-stream,text/plain|max:10240',
            'file_link' => 'nullable|string|max:2000',
            'file_name' => 'required|string|max:150',
            'related_id' => 'required|integer',
        ]);

        $filePath = null;
        if ($request->file_type === 'file upload') {
            if ($request->hasFile('file_upload')) {
                $file = $request->file('file_upload');
                $originalExtension = $file->getClientOriginalExtension();
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $dealFolder = 'deal_files/' . $request->related_id;
                $filename = $originalName . '_' . time() . '.' . $originalExtension;

                //$folder = 'avatars/' . $user->id;
                // $filename = $originalName . '_' . time() . '.' . $originalExtension;
                $relativeDir = 'assets/deals/' . $dealFolder;

                $destination = public_path($relativeDir);
                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                $file->move($destination, $filename);
                /* ===============================
                FILE PATHS
                ================================ */

                // Relative path (store in DB)
                $filePath = $relativeDir . '/' . $filename;
                File::create([
                    'file_type' => $request->file_type,
                    'file_path' => $filePath,
                    'file_name' => $request->file_name,
                    'related_type' => 'deal',
                    'related_id' => $request->related_id,
                    'uploaded_by' => Auth::id(),
                ]);
            }
        } elseif ($request->file_type === 'file links') {
            if (!empty($request->file_link)) {
                $links = preg_split('/[\n,]+/', $request->file_link);
                $count = 0;
                foreach ($links as $link) {
                    $link = trim($link);
                    if (filter_var($link, FILTER_VALIDATE_URL)) {
                        File::create([
                            'file_type' => $request->file_type,
                            'file_path' => $link,
                            'file_name' => $request->file_name . ($count > 0 ? ' #' . ($count+1) : ''),
                            'related_type' => 'deal',
                            'related_id' => $request->related_id,
                            'uploaded_by' => Auth::id(),
                        ]);
                        $count++;
                    }
                }
            }
        }

        return redirect()->back()->with(['success' => 'File(s) added successfully', 'show_files_tab' => true]);
    }

    public function destroy(File $file)
    {
        if ($file->file_type === 'file upload' && $file->file_path) {
            Storage::disk('public')->delete($file->file_path);
        }
        $file->delete();
        return redirect()->back()->with('success', 'File deleted successfully');
    }

    public function download(File $file)
    {
        if ($file->file_type === 'file upload' && $file->file_path) {

            // Full file path
            $filePath = public_path($file->file_path);

            if (file_exists($filePath)) {

                // Get extension from stored file
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);

                // Clean filename
                $baseName = pathinfo($file->file_name, PATHINFO_FILENAME);
                $downloadName = $baseName . ($extension ? '.' . $extension : '');

                return response()->download($filePath, $downloadName);
            }

            return redirect()->back()->with('error', 'File not found.');
        }

        return redirect()->back()->with('error', 'Download not available for links.');
    }
}
