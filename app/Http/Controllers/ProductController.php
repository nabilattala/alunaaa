<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'user']);

        if ($request->has('search')) {
            $query->search($request->search);
        }
        $query->filter($request->only([
            'category_id',
            'min_price',
            'max_price',
            'user_id',
            'status'
        ]));

        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->input('per_page', 10);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'url' => 'nullable|url',
            'image' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:active,inactive',
        ];

        if (auth()->user()->role === 'admin') {
            $rules['price'] = 'nullable|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['image', 'price']);
        $data['user_id'] = auth()->id();

        if (auth()->user()->role !== 'admin') {
            $data['price'] = 0;
        } else {
            $data['price'] = $request->input('price', 0);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return new ProductResource($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if (auth()->user()->role === 'kelas' && $product->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $rules = [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'url' => 'sometimes|url',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'category_id' => 'sometimes|exists:categories,id',
            'status' => 'sometimes|in:active,inactive',
        ];

        if (auth()->user()->role === 'admin') {
            $rules['price'] = 'nullable|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['image', 'price']);

        if (auth()->user()->role === 'admin' && $request->has('price')) {
            $data['price'] = $request->input('price');
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        Log::info('Product updated', ['id' => $product->id, 'price' => $product->price]);

        return new ProductResource($product);
    }

    public function updatePrice(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Pastikan hanya admin yang bisa mengubah harga
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $product->update([
            'price' => $request->price
        ]);

        return response()->json([
            'message' => 'Harga produk berhasil diperbarui.',
            'product' => new ProductResource($product)
        ]);
    }


    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
