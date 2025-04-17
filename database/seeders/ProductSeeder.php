<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $users = User::where('role', 'kelas')->get(); // Ambil user dengan role "kelas"

        $products = [
            [
                'title' => 'Mouse Wireless Logitech M170',
                'description' => 'Mouse wireless dengan desain ergonomis dan koneksi stabil hingga 10 meter.',
                'url' => 'https://example.com/mouse-logitech-m170',
                'price' => 150000,
            ],
            [
                'title' => 'Keyboard Mechanical Rexus Legionare MX5.2',
                'description' => 'Keyboard mechanical dengan switch Outemu Blue dan lampu RGB.',
                'url' => 'https://example.com/rexus-mx5',
                'price' => 450000,
            ],
            [
                'title' => 'Headset Gaming Rexus Vonix F30',
                'description' => 'Headset gaming dengan suara jernih dan mic fleksibel.',
                'url' => 'https://example.com/rexus-vonix-f30',
                'price' => 200000,
            ],
            [
                'title' => 'Flashdisk Sandisk 32GB',
                'description' => 'Flashdisk USB 3.0 dengan kecepatan transfer tinggi.',
                'url' => 'https://example.com/sandisk-32gb',
                'price' => 80000,
            ],
            [
                'title' => 'Kabel Data Vivan Type-C',
                'description' => 'Kabel data fast charging dengan panjang 1 meter.',
                'url' => 'https://example.com/vivan-typec',
                'price' => 50000,
            ],
        ];

        foreach ($categories as $category) {
            foreach ($products as $product) {
                Product::create([
                    'title' => $product['title'],
                    'description' => $product['description'],
                    'url' => $product['url'],
                    'category_id' => $category->id,
                    'user_id' => $users->random()->id,
                    'price' => $product['price'],
                ]);
            }
        }
    }
}
