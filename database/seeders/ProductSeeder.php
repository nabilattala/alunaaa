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

        foreach ($categories as $category) {
            Product::create([
                'title' => 'Sample Product',
                'description' => 'This is a sample product description.',
                'url' => 'https://example.com',
                'category_id' => $category->id,
                'user_id' => $users->random()->id,
            ]);
        }
    }
}

