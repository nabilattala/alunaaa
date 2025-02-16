<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        Banner::insert([
            [
                'title' => 'Selamat Datang di Aluna Store',
                'description' => 'Tempat terbaik untuk mendapatkan software edukasi berkualitas.',
                'image' => 'banners/banner1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Promo Software Gratis!',
                'description' => 'Dapatkan software gratis dengan kupon spesial.',
                'image' => 'banners/banner2.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
