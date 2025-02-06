<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Midtrans;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$clientKey = env('MIDTRANS_CLIENT_KEY');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    // Fungsi untuk membuat transaksi
    public function createTransaction(Request $request)
    {
        $transaction_details = array(
            'order_id' => 'order-' . rand(),
            'gross_amount' => $request->amount, // Total pembayaran
        );

        $item_details = array(
            array(
                'id' => 'item-1',
                'price' => $request->amount,
                'quantity' => 1,
                'name' => 'Aluna Store Purchase',
            ),
        );

        $customer_details = array(
            'first_name'    => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
        );

        $transaction_data = array(
            'transaction_details' => $transaction_details,
            'item_details'        => $item_details,
            'customer_details'    => $customer_details,
        );

        try {
            // Mendapatkan token transaksi
            $snapToken = Snap::getSnapToken($transaction_data);

            return response()->json(['snap_token' => $snapToken]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Fungsi untuk memverifikasi status pembayaran
    public function paymentStatus(Request $request)
{
    try {
        $order_id = $request->order_id;
        $status = Transaction::status($order_id); // ✅ Gunakan Transaction::status()
        return response()->json($status);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}

