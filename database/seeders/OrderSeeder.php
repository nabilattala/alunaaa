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
            'order_id' => 'ORD-001',
            'product_id' => $products[0]->id,
            'user_id' => $users[0]->id,
            'status' => 'pending',
            'total_price' => 50000,
        ]);

        Order::create([
            'order_id' => 'ORD-002',
            'product_id' => $products[1]->id,
            'user_id' => $users[1]->id,
            'status' => 'completed',
            'total_price' => 75000,
        ]);
    }
}
