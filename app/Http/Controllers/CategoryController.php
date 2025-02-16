<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        // Implement search functionality
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where('name', 'LIKE', "%{$searchTerm}%");
        }

        // Add sorting functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Add per_page customization
        $perPage = $request->get('per_page', 10);
        
        return CategoryResource::collection($query->paginate($perPage));
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        return new CategoryResource($category);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string|max:1000', // Tambahan field baru
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = Category::create($validator->validated());
        return new CategoryResource($category);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string|max:1000', // Tambahan field baru
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($validator->validated());
        return new CategoryResource($category);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        
        // Check if category has related products
        if ($category->products()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated products'
            ], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }

    // New method to get categories with product count
    public function getCategoriesWithProductCount()
    {
        $categories = Category::withCount('products')->get();
        return CategoryResource::collection($categories);
    }
}