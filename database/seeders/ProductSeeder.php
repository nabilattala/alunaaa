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
                'title' => 'TechCrunch',
                'description' => 'Website berita teknologi terkemuka yang membahas startup, gadget, dan dunia digital.',
                'url' => 'https://techcrunch.com',
                'video_url' => 'https://www.youtube.com/watch?v=techcrunch-video',
                'price' => 0,
            ],
            [
                'title' => 'Netflix',
                'description' => 'Layanan streaming film dan serial terpopuler di dunia.',
                'url' => 'https://www.netflix.com',
                'video_url' => 'https://www.youtube.com/watch?v=netflix-trailer',
                'price' => 149000,
            ],
            [
                'title' => 'Shopee',
                'description' => 'Marketplace online dengan berbagai produk dari kebutuhan harian hingga elektronik.',
                'url' => 'https://shopee.co.id',
                'video_url' => 'https://www.youtube.com/watch?v=shopee-commercial',
                'price' => 0,
            ],
            [
                'title' => 'Canva',
                'description' => 'Platform desain grafis online yang mudah digunakan untuk membuat berbagai jenis desain.',
                'url' => 'https://www.canva.com',
                'video_url' => 'https://www.youtube.com/watch?v=canva-guide',
                'price' => 95000,
            ],
            [
                'title' => 'LinkedIn',
                'description' => 'Jejaring sosial profesional untuk mencari kerja, koneksi bisnis, dan berbagi informasi.',
                'url' => 'https://www.linkedin.com',
                'video_url' => 'https://www.youtube.com/watch?v=linkedin-tips',
                'price' => 0,
            ],
        ];

        foreach ($categories as $category) {
            foreach ($products as $product) {
                Product::create([
                    'title' => $product['title'],
                    'description' => $product['description'],
                    'url' => $product['url'],
                    'video_url' => $product['video_url'],
                    'category_id' => $category->id,
                    'user_id' => $users->random()->id,
                    'price' => $product['price'],
                    'status' => 'active',
                ]);
            }
        }
    }
}
