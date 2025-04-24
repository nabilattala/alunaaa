<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Discount;
use App\Models\PriceRequest;
use App\Exports\ProductExport;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with([
            'discounts' => function ($query) {
                $query->whereDate('expires_at', '>=', now());
            },
            'ratings',
            'category',
            'user'
        ])->get();

        return response()->json([
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'description' => $product->description,
                    'price' => $product->price,
                    'user' => [
                        'id' => $product->user_id,
                        'username' => $product->user->username ?? null,
                    ],
                    'creator_name' => $product->user->username ?? null,
                    'category' => [
                        'id' => $product->category->id ?? null,
                        'name' => $product->category->name ?? null,
                    ],
                    'image_path' => $product->images_path,
                    'image_url' => $product->images_url ?? ($product->images_path ? 'storage/' . $product->images_path : null),
                    'discounts' => $product->discounts->map(function ($discount) {
                        return [
                            'code' => $discount->code,
                            'percentage' => $discount->percentage,
                            'expires_at' => $discount->expires_at,
                        ];
                    }),
                    'final_price' => $product->discounts->isNotEmpty()
                        ? $product->price - ($product->price * ($product->discounts->first()->percentage / 100))
                        : $product->price,
                    'average_rating' => $product->ratings->avg('rating') ? round($product->ratings->avg('rating'), 1) : null,
                    'url' => $product->url,
                    'video_url' => $product->video_url,
                    'status' => $product->status,
                    'created_at' => $product->created_at->format('d-m-Y H:i'),
                    'updated_at' => $product->updated_at->format('d-m-Y H:i'),
                ];
            })
        ]);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'discounts', 'ratings', 'user'])->find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $product->id,
                'title' => $product->title,
                'description' => $product->description,
                'price' => $product->price,
                'user_id' => $product->user_id,
                'creator_name' => $product->user->username ?? null,
                'category' => [
                    'id' => $product->category->id ?? null,
                    'name' => $product->category->name ?? null,
                ],
                'discounts' => $product->discounts->map(function ($discount) {
                    return [
                        'code' => $discount->code,
                        'percentage' => $discount->percentage,
                        'expires_at' => $discount->expires_at,
                    ];
                }),
                'final_price' => $product->discounts->isNotEmpty()
                    ? $product->price - ($product->price * ($product->discounts->first()->percentage / 100))
                    : $product->price,
                'image' => $product->images_url ?? ($product->images_path ? 'storage/' . $product->images_path : null),
                'url' => $product->url,
                'video_url' => $product->video_url,
                'status' => $product->status,
                'average_rating' => $product->ratings->avg('rating') ? round($product->ratings->avg('rating'), 1) : null,
                'created_at' => $product->created_at->format('d-m-Y H:i'),
                'updated_at' => $product->updated_at->format('d-m-Y H:i'),
            ]
        ]);
    }
    public function store(Request $request)
    {
        $user = auth()->user();
    
        if (!in_array($user->role, ['admin', 'kelas'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'url' => 'nullable|url',
            'video_url' => 'nullable|url',
            'image' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:active,inactive',
        ];
    
        if ($user->role === 'admin') {
            $rules['price'] = 'nullable|numeric|min:0';
        }
    
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $data = $request->except(['image', 'price']);
        $data['user_id'] = $user->id;
        $data['price'] = ($user->role === 'admin') ? $request->input('price', 0) : 0;
    
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['images_path'] = $path;
            $data['images_url'] = url('storage/' . $path);  // Mengubah ke URL lengkap
        }
    
        $product = Product::create($data);
    
        return new ProductResource($product);
    }
    
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $user = auth()->user();
    
        if ($user->role === 'kelas' && $product->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        $rules = [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'url' => 'sometimes|url',
            'video_url' => 'sometimes|url',
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
            if ($product->images_path) {
                Storage::disk('public')->delete($product->images_path);
            }
    
            $path = $request->file('image')->store('products', 'public');
            $data['images_path'] = $path;
            $data['images_url'] = url('storage/' . $path);  // Mengubah ke URL lengkap
        }
    
        $product->update($data);
    
        Log::info('Product updated', [
            'id' => $product->id,
            'title' => $product->title,
            'price' => $product->price,
            'updated_by' => $user->id,
        ]);
    
        return new ProductResource($product);
    }
    
    public function requestPrice(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $user = auth()->user();

        if ($user->role !== 'kelas' || $product->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (PriceRequest::where('product_id', $id)->where('status', 'pending')->exists()) {
            return response()->json(['message' => 'Anda sudah mengajukan request harga, tunggu persetujuan admin.'], 400);
        }

        $priceRequest = PriceRequest::create([
            'product_id' => $id,
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Permintaan harga berhasil dikirim.',
            'price_request' => $priceRequest
        ]);
    }

    public function approvePriceRequest(Request $request, $id)
    {
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $priceRequest = PriceRequest::findOrFail($id);
        $product = Product::findOrFail($priceRequest->product_id);

        $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $product->update(['price' => $request->price]);
        $priceRequest->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Harga berhasil diperbarui oleh admin.',
            'product' => new ProductResource($product)
        ]);
    }

    public function applyDiscount(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string|exists:discounts,code'
        ]);

        $product = Product::findOrFail($id);
        $discount = Discount::where('code', $request->code)
                            ->whereDate('expires_at', '>=', now())
                            ->first();

        if (!$discount) {
            return response()->json(['message' => 'Diskon tidak valid atau sudah kedaluwarsa.'], 400);
        }

        $product->discounts()->syncWithoutDetaching([$discount->id]);

        return response()->json([
            'message' => 'Diskon berhasil diterapkan.',
            'discount' => $discount
        ]);
    }
}
