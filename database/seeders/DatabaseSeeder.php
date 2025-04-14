<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Jalankan seeder sesuai urutan dependensinya
        $this->call([
            // Seeder untuk roles harus duluan
            RoleSeeder::class,

            // Seeder untuk users, karena user butuh role
            UserSeeder::class,

            // Seeder lain yang tidak bergantung user
            CategorySeeder::class,
            BannerSeeder::class,
            AboutSeeder::class,

            // Seeder untuk produk harus dijalankan sebelum order
            ProductSeeder::class,  // <-- Tambahkan ProductSeeder sebelum OrderSeeder

            // Seeder untuk orders, karena butuh user & produk
            OrderSeeder::class,
        ]);
    }
}
