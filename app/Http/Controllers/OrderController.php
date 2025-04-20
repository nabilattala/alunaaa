<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Mail\InvoiceMail;
use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $user = auth()->user();

        // Cek checkout dari keranjang atau langsung
        if ($request->has('cart')) {
            $cartItems = Cart::where('user_id', $user->id)->get();
            if ($cartItems->isEmpty()) {
                return response()->json(['message' => 'Cart is empty'], 400);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'username' => $user->username,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'total_price' => $cartItems->sum(fn($item) => $item->product->price * $item->quantity),
                'status' => 'pending',
                'payment_status' => 'unpaid'
            ]);

            foreach ($cartItems as $item) {
                // Menambahkan item ke tabel order_items
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            // Hapus keranjang setelah checkout
            Cart::where('user_id', $user->id)->delete();
        } else {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $product = Product::findOrFail($request->product_id);
            $order = Order::create([
                'user_id' => $user->id,
                'username' => $user->username,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'total_price' => $product->price * $request->quantity,
                'status' => 'pending',
                'payment_status' => 'unpaid'
            ]);

            // Menambahkan item ke tabel order_items
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price,
            ]);
        }

        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

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

        // Kirim invoice via email
        Mail::to($user->email)->send(new InvoiceMail($order));

        return response()->json([
            'snap_token' => $snapToken,
            'message' => 'Order created and invoice sent!'
        ]);
    }


}
