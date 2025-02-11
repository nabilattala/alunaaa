<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }


    public function index()
    {
        return response()->json(Order::with(['user', 'product'])->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $product = Product::findOrFail($request->product_id);

        $order = Order::create([
            'user_id' => $request->user_id,
            'product_id' => $product->id,
            'order_id' => 'ORD-' . time(),
            'total_price' => $product->price,
            'status' => 'pending',
        ]);

        $transaction_details = [
            'order_id' => $order->order_id,
            'gross_amount' => $order->total_price,
        ];

        $customer_details = [
            'first_name' => $order->user->name,
            'email' => $order->user->email,
        ];

        $midtrans_payload = [
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
        ];

        $snapToken = Snap::getSnapToken($midtrans_payload);

        return response()->json([
            'order' => $order,
            'snap_token' => $snapToken,
        ]);
    }

    public function show(Order $order)
    {
        return response()->json($order->load(['user', 'product']));
    }

    public function updateStatus(Request $request, $order_id)
    {
        $order = Order::where('order_id', $order_id)->firstOrFail();
        $order->update([
            'status' => $request->status,
        ]);
        return response()->json(['message' => 'Order updated successfully']);
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
