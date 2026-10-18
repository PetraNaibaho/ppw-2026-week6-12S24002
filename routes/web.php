<?php

use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Informasi Perpustakaan Kampus Del (SIPUS-Del)
|--------------------------------------------------------------------------
|
| Routing RESTful terstruktur menggunakan Route::resource() yang mendaftarkan
| 7 aksi baku CRUD untuk entitas Buku dan Kategori, serta rute sirkulasi
| transaksi peminjaman buku berbasis ACID.
|
*/

// Redirect halaman utama ke daftar katalog buku
Route::get('/', function () {
    return redirect()->route('buku.index');
});

// Resource Controller untuk Entitas Kategori Buku (7 method CRUD)
Route::resource('kategori', KategoriController::class);

// Resource Controller untuk Entitas Buku (7 method CRUD)
Route::resource('buku', BukuController::class);

// Rute Sirkulasi Peminjaman & Pengembalian Buku Berbasis Transaksi ACID
Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
Route::patch('/peminjaman/{id}/kembali', [PeminjamanController::class, 'kembali'])->name('peminjaman.kembali');
