<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Buat roles jika belum ada
         $roles = ['admin'];
         foreach ($roles as $roleName) {
             Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);
         }
 
         // Buat pengguna dan assign role
         $admin = User::create([
             'username' => 'Admin',
             'email' => 'admin@gmail.com',
             'role' => 'admin',
             'password' => bcrypt('password'),
         ]);
         // Buat pengguna dan assign role
         $admin = User::create([
            'username' => 'kelas',
            'email' => 'kelas@gmail.com',
            'role' => 'kelas',
            'password' => bcrypt('password123'),
        ]);
        $admin->assignRole('admin');
    }
}
