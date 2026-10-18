<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    /**
     * Menampilkan daftar sirkulasi transaksi peminjaman buku.
     */
    public function index(Request $request): View
    {
        if ($request->filled('search')) {
            session(['last_peminjaman_search' => $request->search]);
        }

        $query = Peminjaman::with(['buku.kategori']);

        // Filter status ('dipinjam' / 'dikembalikan')
        $query->when($request->filled('status') && in_array($request->status, ['dipinjam', 'dikembalikan']), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        // Filter pencarian berdasarkan NIM, nama, kode pinjam, atau judul buku
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sub) use ($search) {
                $sub->where('nim_peminjam', 'like', "%{$search}%")
                    ->orWhere('nama_peminjam', 'like', "%{$search}%")
                    ->orWhere('kode_pinjam', 'like', "%{$search}%")
                    ->orWhereHas('buku', function ($bq) use ($search) {
                        $bq->where('judul', 'like', "%{$search}%")
                           ->orWhere('isbn', 'like', "%{$search}%");
                    });
            });
        });

        $peminjamans = $query->latest('tanggal_pinjam')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('peminjaman.index', compact('peminjamans'));
    }

    /**
     * Menampilkan formulir pencatatan peminjaman buku baru.
     */
    public function create(Request $request): View
    {
        $selectedBukuId = $request->query('buku_id');
        $bukus = Buku::with('kategori')->orderBy('judul')->get();

        return view('peminjaman.create', compact('bukus', 'selectedBukuId'));
    }

    /**
     * Memproses transaksi peminjaman buku secara ACID dengan Pessimistic Locking (lockForUpdate).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'buku_id' => ['required', 'exists:bukus,id'],
            'nim_peminjam' => ['required', 'string', 'min:6', 'max:15'],
            'nama_peminjam' => ['required', 'string', 'min:3', 'max:150'],
            'tanggal_pinjam' => ['required', 'date'],
            'durasi_hari' => ['nullable', 'integer', 'min:1', 'max:30'],
        ], [
            'buku_id.required' => 'Buku yang dipinjam wajib dipilih.',
            'buku_id.exists' => 'Buku yang dipilih tidak ditemukan di sistem.',
            'nim_peminjam.required' => 'NIM mahasiswa peminjam wajib diisi.',
            'nim_peminjam.min' => 'NIM mahasiswa minimal 6 karakter.',
            'nama_peminjam.required' => 'Nama lengkap peminjam wajib diisi.',
            'tanggal_pinjam.required' => 'Tanggal peminjaman wajib ditentukan.',
        ]);

        try {
            $peminjaman = DB::transaction(function () use ($validated) {
                // 1. Kunci baris buku menggunakan Pessimistic Locking (SELECT ... FOR UPDATE)
                $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->firstOrFail();

                // 2. Evaluasi ketersediaan stok
                if ($buku->stok < 1) {
                    throw new \RuntimeException("Stok buku '{$buku->judul}' habis (0 eksemplar). Transaksi peminjaman dibatalkan secara atomik!");
                }

                $nim = strtoupper(trim($validated['nim_peminjam']));

                // 3. Pencegahan duplikasi: mahasiswa tidak boleh meminjam buku yang sama jika belum mengembalikannya
                $hasActiveLoan = Peminjaman::where('buku_id', $buku->id)
                    ->where('nim_peminjam', $nim)
                    ->where('status', 'dipinjam')
                    ->lockForUpdate()
                    ->exists();

                if ($hasActiveLoan) {
                    throw new \RuntimeException("Mahasiswa dengan NIM {$nim} masih memiliki catatan peminjaman aktif untuk buku '{$buku->judul}' yang belum dikembalikan!");
                }

                // 4. Pengurangan stok buku secara atomik
                $buku->decrement('stok');

                // 5. Generate kode transaksi unik & hitung tanggal jatuh tempo
                $kodePinjam = 'PJ-' . date('Ymd') . '-' . strtoupper(Str::random(5));
                $durasi = (int) ($validated['durasi_hari'] ?? 7);
                $tanggalPinjam = Carbon::parse($validated['tanggal_pinjam']);
                $tanggalJatuhTempo = $tanggalPinjam->copy()->addDays($durasi);

                // 6. Simpan informasi peminjam ke session state
                session([
                    'last_borrower_nim' => $nim,
                    'last_borrower_name' => $validated['nama_peminjam'],
                ]);

                // 7. Simpan entri transaksi peminjaman
                return Peminjaman::create([
                    'kode_pinjam' => $kodePinjam,
                    'buku_id' => $buku->id,
                    'nim_peminjam' => $nim,
                    'nama_peminjam' => trim($validated['nama_peminjam']),
                    'tanggal_pinjam' => $tanggalPinjam->toDateString(),
                    'tanggal_jatuh_tempo' => $tanggalJatuhTempo->toDateString(),
                    'tanggal_kembali' => null,
                    'status' => 'dipinjam',
                ]);
            }, 3);

            return redirect()->route('peminjaman.index')
                ->with('success', "Transaksi peminjaman [{$peminjaman->kode_pinjam}] untuk buku '{$peminjaman->buku->judul}' berhasil dicatat secara atomik!");
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION - PINJAM BUKU]: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan basis data saat memproses peminjaman.');
        }
    }

    /**
     * Memproses pengembalian buku secara ACID dengan Pessimistic Locking (lockForUpdate).
     */
    public function kembali(Request $request, int $id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($id) {
                // 1. Kunci baris peminjaman menggunakan lockForUpdate()
                $peminjaman = Peminjaman::where('id', $id)->lockForUpdate()->firstOrFail();

                if ($peminjaman->status === 'dikembalikan') {
                    throw new \RuntimeException("Buku pada transaksi [{$peminjaman->kode_pinjam}] sudah pernah dikembalikan sebelumnya.");
                }

                // 2. Kunci baris buku untuk update stok aman
                $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->firstOrFail();

                // 3. Update status dan tanggal pengembalian
                $peminjaman->update([
                    'status' => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);

                // 4. Pemulihan stok buku secara atomik
                $buku->increment('stok');
            }, 3);

            return back()->with('success', 'Buku berhasil dikembalikan dan stok buku telah dipulihkan secara atomik!');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION - KEMBALI BUKU]: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan basis data saat memproses pengembalian.');
        }
    }
}
