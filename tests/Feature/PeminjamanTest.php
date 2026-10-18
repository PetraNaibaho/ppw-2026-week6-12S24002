<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanTest extends TestCase
{
    use RefreshDatabase;

    private Kategori $kategori;
    private Buku $buku;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kategori = Kategori::create([
            'kode_kategori' => 'RPL',
            'nama_kategori' => 'Rekayasa Perangkat Lunak',
        ]);

        $this->buku = Buku::create([
            'isbn' => '9786020000011',
            'judul' => 'Basis Data Lanjut',
            'penulis' => 'Chandro Pardede',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2024,
            'kategori_id' => $this->kategori->id,
            'stok' => 2,
            'sinopsis' => 'Buku teks basis data dan transaksi ACID.',
        ]);

        $user = \App\Models\User::create([
            'name' => 'Pustakawan Uji',
            'email' => 'pustakawan@del.ac.id',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        $this->actingAs($user);
    }

    /**
     * Uji peminjaman berhasil mengurangi stok secara atomik.
     */
    public function test_can_borrow_book_atomically_and_decrement_stock(): void
    {
        $response = $this->post(route('peminjaman.store'), [
            'buku_id' => $this->buku->id,
            'nim_peminjam' => '12S24002',
            'nama_peminjam' => 'Petra Naibaho',
            'tanggal_pinjam' => now()->toDateString(),
            'durasi_hari' => 7,
        ]);

        $response->assertRedirect(route('peminjaman.index'));
        $response->assertSessionHas('success');

        // Pastikan record peminjaman tercipta
        $this->assertDatabaseHas('peminjamans', [
            'buku_id' => $this->buku->id,
            'nim_peminjam' => '12S24002',
            'nama_peminjam' => 'Petra Naibaho',
            'status' => 'dipinjam',
        ]);

        // Pastikan stok buku berkurang 1 (2 -> 1)
        $this->assertEquals(1, $this->buku->fresh()->stok);

        // Pastikan session state pencatat peminjam terakhir tersimpan
        $this->assertEquals('12S24002', session('last_borrower_nim'));
    }

    /**
     * Uji penolakan peminjaman dan rollback atomik ketika stok habis (stok = 0).
     */
    public function test_cannot_borrow_book_when_stock_is_zero(): void
    {
        // Ubah stok buku menjadi 0
        $this->buku->update(['stok' => 0]);

        $response = $this->from(route('peminjaman.create'))->post(route('peminjaman.store'), [
            'buku_id' => $this->buku->id,
            'nim_peminjam' => '12S24002',
            'nama_peminjam' => 'Petra Naibaho',
            'tanggal_pinjam' => now()->toDateString(),
            'durasi_hari' => 7,
        ]);

        $response->assertRedirect(route('peminjaman.create'));
        $response->assertSessionHas('error');

        // Pastikan tidak ada data yang masuk ke database
        $this->assertDatabaseCount('peminjamans', 0);
        $this->assertEquals(0, $this->buku->fresh()->stok);
    }

    /**
     * Uji penolakan peminjaman ganda oleh mahasiswa yang sama pada buku yang belum dikembalikan.
     */
    public function test_cannot_borrow_same_book_twice_without_returning(): void
    {
        // Peminjaman pertama
        $this->post(route('peminjaman.store'), [
            'buku_id' => $this->buku->id,
            'nim_peminjam' => '12S24002',
            'nama_peminjam' => 'Petra Naibaho',
            'tanggal_pinjam' => now()->toDateString(),
            'durasi_hari' => 7,
        ]);

        $this->assertEquals(1, $this->buku->fresh()->stok);

        // Peminjaman kedua oleh NIM yang sama untuk buku yang sama
        $response = $this->from(route('peminjaman.create'))->post(route('peminjaman.store'), [
            'buku_id' => $this->buku->id,
            'nim_peminjam' => '12S24002',
            'nama_peminjam' => 'Petra Naibaho',
            'tanggal_pinjam' => now()->toDateString(),
            'durasi_hari' => 7,
        ]);

        $response->assertRedirect(route('peminjaman.create'));
        $response->assertSessionHas('error');

        // Stok tidak boleh berkurang lagi
        $this->assertEquals(1, $this->buku->fresh()->stok);
        $this->assertDatabaseCount('peminjamans', 1);
    }

    /**
     * Uji pengembalian buku memulihkan stok buku secara atomik.
     */
    public function test_can_return_book_atomically_and_increment_stock(): void
    {
        // Buat transaksi peminjaman
        $this->post(route('peminjaman.store'), [
            'buku_id' => $this->buku->id,
            'nim_peminjam' => '12S24002',
            'nama_peminjam' => 'Petra Naibaho',
            'tanggal_pinjam' => now()->toDateString(),
            'durasi_hari' => 7,
        ]);

        $peminjaman = Peminjaman::first();
        $this->assertEquals(1, $this->buku->fresh()->stok);
        $this->assertEquals('dipinjam', $peminjaman->status);

        // Eksekusi pengembalian
        $response = $this->from(route('peminjaman.index'))->patch(route('peminjaman.kembali', $peminjaman->id));

        $response->assertRedirect(route('peminjaman.index'));
        $response->assertSessionHas('success');

        // Pastikan status peminjaman berubah dan stok kembali ke 2
        $peminjaman->refresh();
        $this->assertEquals('dikembalikan', $peminjaman->status);
        $this->assertNotNull($peminjaman->tanggal_kembali);
        $this->assertEquals(2, $this->buku->fresh()->stok);
    }
}
