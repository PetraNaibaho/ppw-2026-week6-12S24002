<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Informasi Perpustakaan Kampus Del (SIPUS-Del)
|--------------------------------------------------------------------------
|
| Routing RESTful terstruktur yang mengelola katalog publik (Read-Only)
| dan memproteksi seluruh rute mutasi inventaris serta transaksi sirkulasi
| peminjaman buku berbasis ACID menggunakan Middleware 'auth'.
|
*/

// Redirect root URI ke katalog koleksi buku
Route::get('/', function () {
    return redirect()->route('buku.index');
});

// ==========================================
// RUTE AUTENTIKASI TAMU (Guest Only)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ==========================================
// RUTE MUTASI TERPROTEKSI (Auth Middleware)
// Didaftarkan sebelum wildcard {id} publik agar route /create tidak tertabrak
// ==========================================
Route::middleware('auth')->group(function () {
    // Sesi Keluar Pustakawan
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Mutasi Entitas Kategori
    Route::get('/kategori/create', [KategoriController::class, 'create'])->name('kategori.create');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::get('/kategori/{kategori}/edit', [KategoriController::class, 'edit'])->name('kategori.edit');
    Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

    // Mutasi Entitas Buku
    Route::get('/buku/create', [BukuController::class, 'create'])->name('buku.create');
    Route::post('/buku', [BukuController::class, 'store'])->name('buku.store');
    Route::get('/buku/{buku}/edit', [BukuController::class, 'edit'])->name('buku.edit');
    Route::put('/buku/{buku}', [BukuController::class, 'update'])->name('buku.update');
    Route::delete('/buku/{buku}', [BukuController::class, 'destroy'])->name('buku.destroy');

    // Sirkulasi Peminjaman & Pengembalian Berbasis Transaksi ACID
    Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
    Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    Route::patch('/peminjaman/{id}/kembali', [PeminjamanController::class, 'kembali'])->name('peminjaman.kembali');
});

// ==========================================
// RUTE PUBLIK (Katalog Buku & Kategori Read-Only)
// ==========================================
Route::get('/buku', [BukuController::class, 'index'])->name('buku.index');
Route::get('/buku/{buku}', [BukuController::class, 'show'])->name('buku.show');
Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
Route::get('/kategori/{kategori}', [KategoriController::class, 'show'])->name('kategori.show');
