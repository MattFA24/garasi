<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Membuat akun admin. Admin tidak bisa daftar lewat form,
     * jadi akunnya dibuat lewat seeder. 
     */
    public function run(): void
    {
        User::create([
            'nik' => '3578000000000001',
            'name' => 'Admin Garasi',
            'phone' => '081234567890',
            'email' => 'admin@garasi.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'approved',
        ]);
    }
}