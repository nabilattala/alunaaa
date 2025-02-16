<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'user']);

        // Pencarian
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filter
        $query->filter($request->only([
            'category_id',
            'min_price',
            'max_price',
            'user_id',
            'status'
        ]));

        // Sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $perPage = $request->input('per_page', 10);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'url' => 'nullable|url',
            'image' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('image');
        $data['user_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return new ProductResource($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Cek apakah user memiliki akses
        if (auth()->user()->role === 'kelas' && $product->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'url' => 'sometimes|url',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'category_id' => 'sometimes|exists:categories,id',
            'price' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return new ProductResource($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Cek apakah user memiliki akses
        if (auth()->user()->role === 'kelas' && $product->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}