<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Midtrans\Snap;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;  // Pastikan sudah memiliki mail class InvoiceMail
use Midtrans\Config;
use Illuminate\Support\Str;

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

            $totalPrice = $cartItems->sum(function ($item) {
                return $item->product->price; // default quantity = 1
            });

            $order = Order::create([
                'user_id' => $user->id,
                'username' => $user->username,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'total_price' => $totalPrice,
                'status' => 'pending',
                'payment_status' => 'unpaid'
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => 1,
                    'price' => $item->product->price,
                ]);
            }

            Cart::where('user_id', $user->id)->delete();
        } else {
            $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);

            $product = Product::findOrFail($request->product_id);

            $order = Order::create([
                'user_id' => $user->id,
                'username' => $user->username,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'total_price' => $product->price,
                'status' => 'pending',
                'payment_status' => 'unpaid'
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
            ]);
        }

        // Midtrans config
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');

        $transaction = [
            'transaction_details' => [
                'order_id' => $order->invoice_number,
                'gross_amount' => $order->total_price,
            ],
            'customer_details' => [
                'email' => $user->email,
                'first_name' => $user->name,
            ],
        ];

        $snapToken = Snap::getSnapToken($transaction);

        // Simpan payment_url
        $order->payment_url = "https://app.sandbox.midtrans.com/snap/v2/vtweb/" . $snapToken;
        $order->save();

        // Kirim email invoice ke user setelah order berhasil
        Mail::to($user->email)->send(new InvoiceMail($order)); // Pastikan untuk membuat InvoiceMail class yang menerima order

        return response()->json([
            'snap_token' => $snapToken,
            'message' => 'Order created, waiting for payment. Invoice has been sent to your email.'
        ]);
    }
}
