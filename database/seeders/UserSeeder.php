<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seeder Akun Uji Pustakawan Resmi SIPUS-Del
        User::updateOrCreate(
            ['email' => 'pustakawan@del.ac.id'],
            [
                'name' => 'Pustakawan Institut Teknologi Del',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
