<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Jalankan Seeder Pustakawan, Kategori, Buku, dan Transaksi Peminjaman (SIPUS-Del Tahap 2)
        $this->call([
            UserSeeder::class,
            KategoriSeeder::class,
            BukuSeeder::class,
            PeminjamanSeeder::class,
        ]);
    }
}
