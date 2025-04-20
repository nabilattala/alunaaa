<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::where('user_id', auth()->id())->with('product.discounts', 'product.ratings', 'product.category', 'product.user')->get();

        return response()->json([
            'cart' => $cartItems->map(function ($item) {
                $product = $item->product;

                return [
                    'cart_id' => $item->id,
                    'quantity' => $item->quantity,
                    'product' => [
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
                        'average_rating' => $product->ratings->avg('rating') ? round($product->ratings->avg('rating'), 1) : null,
                        'created_at' => $product->created_at->format('d-m-Y H:i'),
                        'updated_at' => $product->updated_at->format('d-m-Y H:i'),
                    ]
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::updateOrCreate(
            ['user_id' => auth()->id(), 'product_id' => $request->product_id],
            ['quantity' => $request->quantity]
        );

        return response()->json(['message' => 'Product added to cart', 'cart_id' => $cartItem->id]);
    }

    public function update(Request $request, $id)
    {
        $cartItem = Cart::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['quantity' => 'required|integer|min:1']);

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Cart updated', 'cart_id' => $cartItem->id]);
    }

    public function destroy($id)
    {
        $cartItem = Cart::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $cartItem->delete();

        return response()->json(['message' => 'Product removed from cart']);
    }
}
