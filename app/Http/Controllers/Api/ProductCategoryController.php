<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        $categories = ProductCategory::with('variations')->get();
        return $this->success($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
        ]);

        $category = ProductCategory::create($data);

        return $this->success($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = ProductCategory::find($id);
        return $this->success($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = ProductCategory::find($id);
        $category->update($request->all());
        return $this->success($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = ProductCategory::find($id);
        $category->delete();
        return $this->success($category);
    }
}
