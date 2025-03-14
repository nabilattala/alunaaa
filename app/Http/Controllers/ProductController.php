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
    public function store(Request $request)
    {
        $user = auth()->user();

        // Hanya 'admin' atau 'kelas' yang bisa menambahkan produk
        if (!in_array($user->role, ['admin', 'kelas'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'url' => 'nullable|url',
            'image' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:active,inactive',
        ];

        // Admin bisa menentukan harga, kelas tidak bisa
        if ($user->role === 'admin') {
            $rules['price'] = 'nullable|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['image', 'price']);
        $data['user_id'] = $user->id;

        // Jika bukan admin, harga default = 0
        $data['price'] = ($user->role === 'admin') ? $request->input('price', 0) : 0;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return new ProductResource($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $user = auth()->user();

        // Kelas hanya bisa mengedit produknya sendiri
        if ($user->role === 'kelas' && $product->user_id !== $user->id) {
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

        if ($user->role === 'admin') {
            $rules['price'] = 'nullable|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['image', 'price']);

        if ($user->role === 'admin' && $request->has('price')) {
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
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $product->update(['price' => $request->price]);

        return response()->json([
            'message' => 'Harga produk berhasil diperbarui.',
            'product' => new ProductResource($product)
        ]);
    }

    public function applyDiscount(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string|exists:discounts,code'
        ]);

        $product = Product::findOrFail($id);
        $discount = Discount::where('code', $request->code)->whereDate('expires_at', '>=', now())->first();

        if (!$discount) {
            return response()->json(['message' => 'Invalid or expired discount code'], 400);
        }

        $discountedPrice = $product->price - ($product->price * ($discount->percentage / 100));
        
        return response()->json([
            'original_price' => $product->price,
            'discount_percentage' => $discount->percentage,
            'discounted_price' => $discountedPrice
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
