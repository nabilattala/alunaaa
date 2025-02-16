<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::insert([
            ['name' => 'E-commerce', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Smart School', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Game', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
