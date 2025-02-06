<?php

namespace App\Http\Controllers;

use App\Http\Resources\AboutResource;
use App\Http\Resources\BannerResource;
use App\Http\Resources\ProductResource;
use App\Models\About;
use App\Models\Banner;
use App\Models\Product;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        // Ambil banner pertama
        $banner = Banner::latest()->first();

        // Ambil about pertama
        $about = About::latest()->first();

        // Ambil 5 produk terbaru
        $products = Product::latest()->take(5)->get();

        return response()->json([
            'banner' => $banner ? new BannerResource($banner) : null,
            'about' => $about ? new AboutResource($about) : null,
            'products' => ProductResource::collection($products),
        ]);
    }
}
