<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Menambahkan pengguna admin
        User::create([
            'username' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'), 
            'role' => 'admin',
        ]);

        User::create([
            'username' => 'Agus321',
            'email' => 'agussitorus24@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }
}
