<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    // Index - Manage Document Types
    public function index()
    {
        $documentTypes = DocumentType::latest()->get();
        return view('masters.document-types.index', compact('documentTypes'));
    }    

    // Store Document Type
    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255|unique:document_types',
            'short_name' => 'nullable|string|max:50|unique:document_types',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ], [
            'document_name.unique' => 'A document type with this name already exists.',
            'short_name.unique' => 'A document type with this short name already exists.',
        ]);

        DocumentType::create($validated);

        return redirect()->route('form/document-type/manage')
            ->with('success', 'Document Type created successfully');
    }

    
    public function getById($id)
    {
        $documentType = DocumentType::findOrFail($id);
        return response()->json($documentType);
    }

    // Update Document Type
    public function update(Request $request)
    {
        $documentType = DocumentType::findOrFail($request->id);
        
        $validated = $request->validate([
            'document_name' => 'required|string|max:255|unique:document_types,document_name,' . $documentType->id,
            'short_name' => 'nullable|string|max:50|unique:document_types,short_name,' . $documentType->id,
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ], [
            'document_name.unique' => 'A document type with this name already exists.',
            'short_name.unique' => 'A document type with this short name already exists.',
        ]);

        $documentType->update($validated);

        return redirect()->route('form/document-type/manage')->with('success', 'Document Type updated successfully.');
    }

    // Delete Document Type
    public function destroy(Request $request)
    {
        $documentType = DocumentType::findOrFail($request->id);
        $documentType->delete();
        return redirect()->route('form/document-type/manage')
            ->with('success', 'Document Type deleted successfully');
    }
}
