<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasCrmPermission('manage_crm_product_category_guard')) {
            abort(403, 'Unauthorized action.');
        }
    $categories = ProductCategory::orderBy('category_name')->paginate(15);
    return view('product_categories.index', compact('categories'));
    }

    public function create()
    {

        if (!auth()->user()->hasCrmPermission('create_crm_product_category_guard')) {
            abort(403, 'Unauthorized action.');
        }
        return view('product_categories.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasCrmPermission('create_crm_product_category_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'category_name' => [
                'required','string','max:150',
                'regex:/^[A-Za-z][A-Za-z .\'-]*$/'
            ],
            'description' => 'nullable|string',
            'status' => 'required|boolean',
        ],[
            'category_name.regex' => 'Name must start with a letter and may only contain letters, spaces, dots, apostrophes, and hyphens.',
        ]);
        $validated['created_by'] = auth()->id();
        $validated['created_at'] = now();
        ProductCategory::create($validated);
        return redirect()->route('product_categories.index')->with('success', 'Product category created successfully!');
    }

    public function edit($id)
    {
        if (!auth()->user()->hasCrmPermission('edit_crm_product_category_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $category = ProductCategory::findOrFail($id);
        return view('product_categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasCrmPermission('edit_crm_product_category_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $category = ProductCategory::findOrFail($id);
        $validated = $request->validate([
            'category_name' =>  [
                'required','string','max:150',
                'regex:/^[A-Za-z][A-Za-z .\'-]*$/'
            ],
            'description' => 'nullable|string',
            'status' => 'required|boolean',
        ],[
            'category_name.regex' => 'Name must start with a letter and may only contain letters, spaces, dots, apostrophes, and hyphens.',
        ]);
        $validated['updated_by'] = auth()->id();
        $validated['updated_at'] = now();
        $category->update($validated);
        return redirect()->route('product_categories.index')->with('success', 'Product category updated successfully!');
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasCrmPermission('delete_crm_product_category_guard')) {
            abort(403, 'Unauthorized action.');
        }
        $category = ProductCategory::findOrFail($id);
        $category->deleted_by = auth()->id();
        $category->deleted_at = now();
        $category->save();
        $category->delete();
        return redirect()->route('product_categories.index')->with('success', 'Product category deleted successfully!');
    }
}
