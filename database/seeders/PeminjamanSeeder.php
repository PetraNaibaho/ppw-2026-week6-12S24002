<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\Seeder;

class PeminjamanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bukuCleanCode = Buku::where('isbn', '9786022987101')->first();
        $bukuGoF = Buku::where('isbn', '9786022987102')->first();
        $bukuPardede = Buku::where('isbn', '9786022987103')->first();

        if ($bukuCleanCode) {
            // Transaksi 1: Sedang Dipinjam
            Peminjaman::updateOrCreate(
                ['kode_pinjam' => 'PJ-20261015-DEL01'],
                [
                    'buku_id' => $bukuCleanCode->id,
                    'nim_peminjam' => '12S24001',
                    'nama_peminjam' => 'Samuel Alexander Simanjuntak',
                    'tanggal_pinjam' => '2026-10-15',
                    'tanggal_jatuh_tempo' => '2026-10-22',
                    'tanggal_kembali' => null,
                    'status' => 'dipinjam',
                ]
            );
        }

        if ($bukuGoF) {
            // Transaksi 2: Sudah Dikembalikan
            Peminjaman::updateOrCreate(
                ['kode_pinjam' => 'PJ-20261010-DEL02'],
                [
                    'buku_id' => $bukuGoF->id,
                    'nim_peminjam' => '12S24002',
                    'nama_peminjam' => 'Petra Ignatius Pengayoman Naibaho',
                    'tanggal_pinjam' => '2026-10-10',
                    'tanggal_jatuh_tempo' => '2026-10-17',
                    'tanggal_kembali' => '2026-10-14',
                    'status' => 'dikembalikan',
                ]
            );
        }

        if ($bukuPardede) {
            // Transaksi 3: Sedang Dipinjam
            Peminjaman::updateOrCreate(
                ['kode_pinjam' => 'PJ-20261016-DEL03'],
                [
                    'buku_id' => $bukuPardede->id,
                    'nim_peminjam' => '12S24003',
                    'nama_peminjam' => 'Grace Yohana Hutabarat',
                    'tanggal_pinjam' => '2026-10-16',
                    'tanggal_jatuh_tempo' => '2026-10-23',
                    'tanggal_kembali' => null,
                    'status' => 'dipinjam',
                ]
            );
        }
    }
}
