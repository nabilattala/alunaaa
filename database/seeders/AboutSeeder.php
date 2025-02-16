<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\About;

class AboutSeeder extends Seeder
{
    public function run(): void
    {
        About::create([
            'content' => 'Aluna Store adalah platform terbaik untuk menemukan software edukasi dan kebutuhan sekolah.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
