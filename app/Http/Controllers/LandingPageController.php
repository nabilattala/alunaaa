<?php

namespace App\Http\Controllers;

use App\Http\Resources\AboutResource;
use App\Http\Resources\BannerResource;
use App\Http\Resources\ProductResource;
use App\Models\About;
use App\Models\Banner;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache; 

class LandingPageController extends Controller
{
    public function index()
    {
        try {
            // Cache hasil untuk performa
            $data = Cache::remember('landing_page', 3600, function () {
                return [
                    'banner' => Banner::latest()->first(),
                    'about' => About::latest()->first(),
                    'products' => Product::with('category')  // Eager loading
                        ->where('status', 'active')         // Scope untuk produk aktif
                        ->latest()
                        ->take(5)
                        ->get()
                ];
            });

            return response()->json([
                'banner' => $data['banner'] ? new BannerResource($data['banner']) : null,
                'about' => $data['about'] ? new AboutResource($data['about']) : null,
                'products' => ProductResource::collection($data['products']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching landing page data',
                'error' => config('app.debug') ? $e->getMessage() : 'Server Error'
            ], 500);
        }
    }

    // Tambahan method untuk mendapatkan data terpisah
    public function getBanner()
    {
        $banner = Banner::latest()->first();
        return $banner ? new BannerResource($banner) : response()->json(null);
    }

    public function getAbout()
    {
        $about = About::latest()->first();
        return $about ? new AboutResource($about) : response()->json(null);
    }

    public function getLatestProducts()
    {
        $products = Product::with('category')
            ->where('status', 'active')
            ->latest()
            ->take(5)
            ->get();
        return ProductResource::collection($products);
    }
}
