<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::take(2)->get();
        $users = User::take(2)->get();

        if ($products->count() < 2 || $users->count() < 2) {
            return; // atau bisa throw error
        }

        Order::create([
            // 'orders_id' => 'ORD-001',
            // 'product_id' => $products[0]->id,
            'user_id' => $users[0]->id,
            'status' => 'pending',
            'invoice_number' => '10000',
            'total_price' => 50000,
            'payment_url' => null,
            'midtrans_response' => null,
        ]);

        Order::create([
            // 'orders_id' => 'ORD-002',
            // 'product_id' => $products[1]->id,
            'user_id' => $users[1]->id,
            'invoice_number' => '10001',
            'status' => 'completed',
            'payment_status' => 'paid',
            'total_price' => 75000,
            'payment_url' => 'https://example.com/payment/INV-002',
            'midtrans_response' => json_encode(['transaction_status' => 'settlement']),
        ]);
    }
}
