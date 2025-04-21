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
            'category', // load category
            'user'      // load user
        ])->get();

        return response()->json([
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'description' => $product->description,
                    'price' => $product->price,
                    'user' => [
                        'id' => $product->user_id,                          // ID user
                        'username' => $product->user->username ?? null,     // Username user
                    ],                          // Tambah user_id
                    'creator_name' => $product->user->username ?? null,      // Nama pembuat
                    'category' => [
                        'id' => $product->category->id ?? null,
                        'name' => $product->category->name ?? null,
                    ],
<<<<<<< HEAD
                    'images_url' => $product->images_url,
                    'discounts' => $product->discounts->map(fn($discount) => [
                        'code' => $discount->code,
                        'percentage' => $discount->percentage,
                        'expires_at' => $discount->expires_at,
                    ]),
=======
                    'image_url' => $product->image ? Storage::url($product->image) : null, // <= ini ditambahkan
                    'discounts' => $product->discounts->map(function ($discount) {
                        return [
                            'code' => $discount->code,
                            'percentage' => $discount->percentage,
                            'expires_at' => $discount->expires_at,
                        ];
                    }),
>>>>>>> d1d3ebd3c1795f4b847c9b61b7441c20a5eefbe5
                    'final_price' => $product->discounts->isNotEmpty()
                        ? $product->price - ($product->price * ($product->discounts->first()->percentage / 100))
                        : $product->price,
                    'average_rating' => $product->ratings->avg('rating') ? round($product->ratings->avg('rating'), 1) : null,
                    'image' => $product->image ? url('storage/' . $product->image) : null,
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
<<<<<<< HEAD
                'images_url' => $product->images_url,
                'discounts' => $product->discounts->map(fn($discount) => [
                    'code' => $discount->code,
                    'percentage' => $discount->percentage,
                    'expires_at' => $discount->expires_at,
                ]),
=======
                'discounts' => $product->discounts->map(function ($discount) {
                    return [
                        'code' => $discount->code,
                        'percentage' => $discount->percentage,
                        'expires_at' => $discount->expires_at,
                    ];
                }),
>>>>>>> d1d3ebd3c1795f4b847c9b61b7441c20a5eefbe5
                'final_price' => $product->discounts->isNotEmpty()
                    ? $product->price - ($product->price * ($product->discounts->first()->percentage / 100))
                    : $product->price,
                'image' => $product->image ? url('storage/' . $product->image) : null,
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
<<<<<<< HEAD
            'image' => 'required|image|mimes:jpg,png,jpeg|max:3060',
=======
            'image' => 'required|image|mimes:jpg,png,jpeg|max:2048',
>>>>>>> d1d3ebd3c1795f4b847c9b61b7441c20a5eefbe5
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
<<<<<<< HEAD
            $imagePath = $request->file('image')->store('products', 'public');
            $imageUrl = Storage::disk('public')->url($imagePath);

            $data['images_url'] = $imageUrl;
            $data['images_path'] = $imagePath;
=======
            $data['image'] = $request->file('image')->store('products', 'public');
>>>>>>> d1d3ebd3c1795f4b847c9b61b7441c20a5eefbe5
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
<<<<<<< HEAD
            'video_url' => 'sometimes|url',
=======
            'video_url' => 'sometimes|url', 
>>>>>>> d1d3ebd3c1795f4b847c9b61b7441c20a5eefbe5
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
<<<<<<< HEAD
            // Hapus gambar lama
            if ($product->images_path) {
            Storage::disk('public')->delete($product->images_path);
            }

            $imagePath = $request->file('image')->store('products', 'public');
            $imageUrl = Storage::disk('public')->url($imagePath);

            $data['images_url'] = $imageUrl;
            $data['images_path'] = $imagePath;
=======
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
>>>>>>> d1d3ebd3c1795f4b847c9b61b7441c20a5eefbe5
        }

        $product->update($data);

        Log::info('Product updated', ['id' => $product->id, 'price' => $product->price]);

        return new ProductResource($product);
    }

    public function requestPrice(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $user = auth()->user();

        if ($user->role !== 'kelas' || $product->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Cek apakah ada request yang masih pending
        if (PriceRequest::where('product_id', $id)->where('status', 'pending')->exists()) {
            return response()->json(['message' => 'Anda sudah mengajukan request harga, tunggu persetujuan admin.'], 400);
        }

        // Simpan request harga tanpa menentukan harga
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

        // Validasi input harga
        $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        // Update harga produk
        $product->update(['price' => $request->price]);

        // Set request harga menjadi approved
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

<<<<<<< HEAD
        if ($product->images_path) {
            Storage::disk('public')->delete($product->images_path);
=======
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
>>>>>>> d1d3ebd3c1795f4b847c9b61b7441c20a5eefbe5
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }

    public function exportExcel()
    {
        if (Product::count() === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada produk untuk di-export'
            ], 404);
        }

        $fileName = 'products_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new ProductExport, $fileName);
    }



}