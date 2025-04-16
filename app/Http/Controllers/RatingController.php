<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Product;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Cek apakah user sudah pernah rating produk ini
        if (Rating::where('product_id', $productId)->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Kamu sudah memberikan rating untuk produk ini.'], 400);
        }

        $rating = Rating::create([
            'product_id' => $productId,
            'user_id' => $user->id,
            'rating' => $request->rating,
            'review' => $request->review
        ]);

        return response()->json([
            'message' => 'Rating berhasil ditambahkan!',
            'data' => $rating
        ]);
    }

    public function index($productId)
    {
        // Ambil semua rating untuk produk
        $ratings = Rating::with('user')->where('product_id', $productId)->latest()->get();

        // Hitung rata-rata rating (average_rating)
        $averageRating = $ratings->avg('rating'); // Rata-rata rating
        $averageRating = $averageRating ? round($averageRating, 1) : null; // Pembulatan ke satu desimal, jika tidak ada rating maka null

        return response()->json([
            'product_id' => $productId,
            'average_rating' => $averageRating, // Menambahkan rata-rata rating
            'ratings' => $ratings
        ]);
    }

}

