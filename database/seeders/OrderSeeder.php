<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        Order::insert([
            [
                'user_id' => 3, // ID pengguna yang valid
                'product_id' => 1, // Pastikan ada produk di tabel products
                'order_id' => 'ORD-001',
                'total_price' => 50000,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'product_id' => 2,
                'order_id' => 'ORD-002',
                'total_price' => 75000,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
