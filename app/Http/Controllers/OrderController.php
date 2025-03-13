<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Midtrans\Snap;
use Midtrans\Config;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $user = auth()->user();
        
        if ($request->has('cart')) {
            $cartItems = Cart::where('user_id', $user->id)->get();
            if ($cartItems->isEmpty()) {
                return response()->json(['message' => 'Cart is empty'], 400);
            }
            
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $cartItems->sum(fn($item) => $item->product->price * $item->quantity),
                'status' => 'pending',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }
            
            Cart::where('user_id', $user->id)->delete();
        } else {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);
            
            $product = Product::findOrFail($request->product_id);
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $product->price * $request->quantity,
                'status' => 'pending',
            ]);
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price,
            ]);
        }
        
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $transaction = [
            'transaction_details' => [
                'order_id' => $order->id,
                'gross_amount' => $order->total_price,
            ],
            'customer_details' => [
                'email' => $user->email,
                'first_name' => $user->name,
            ],
        ];

        $snapToken = Snap::getSnapToken($transaction);
        return response()->json(['snap_token' => $snapToken]);
    }
}
