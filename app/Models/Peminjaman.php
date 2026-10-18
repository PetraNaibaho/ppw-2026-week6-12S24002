<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory;

    /**
     * Nama tabel di basis data.
     *
     * @var string
     */
    protected $table = 'peminjamans';

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignment Protection).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_pinjam',
        'buku_id',
        'nim_peminjam',
        'nama_peminjam',
        'tanggal_pinjam',
        'tanggal_jatuh_tempo',
        'tanggal_kembali',
        'status',
    ];

    /**
     * Konversi tipe atribut otomatis (Eloquent Attribute Casting).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_kembali' => 'date',
    ];

    /**
     * Relasi Many-to-One (Inverse): Setiap transaksi peminjaman terikat pada satu Buku.
     */
    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class, 'buku_id');
    }
}
