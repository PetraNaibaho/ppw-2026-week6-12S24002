<x-layout title="Sirkulasi Peminjaman Buku">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-arrow-left-right text-primary me-2"></i>Sirkulasi Peminjaman & Pengembalian
            </h1>
            <p class="text-secondary small mb-0">
                Pencatatan sirkulasi buku secara persisten berbasis transaksi ACID &amp; Pessimistic Row-Level Locking.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('peminjaman.create') }}" class="btn btn-del shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Catat Peminjaman Baru
            </a>
            <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-book me-1"></i> Katalog Buku
            </a>
        </div>
    </div>

    <!-- Filter & Pencarian Sirkulasi -->
    <div class="card card-custom mb-4">
        <div class="card-body p-3">
            <form action="{{ route('peminjaman.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Status Transaksi</option>
                        <option value="dipinjam" {{ request('status') === 'dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                        <option value="dikembalikan" {{ request('status') === 'dikembalikan' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                    </select>
                </div>
                <div class="col-md-7">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan NIM, Nama Mahasiswa, Kode Pinjam, atau Judul Buku..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-del btn-sm w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
            @if(session('last_peminjaman_search'))
                <div class="text-muted mt-2" style="font-size: 0.75rem;">
                    <i class="bi bi-clock-history me-1"></i> Pencarian sesi terakhir: <code>{{ session('last_peminjaman_search') }}</code>
                </div>
            @endif
        </div>
    </div>

    <!-- Tabel Transaksi Peminjaman -->
    <div class="card card-custom">
        <div class="card-header card-header-del d-flex justify-content-between align-items-center py-3">
            <span class="fw-semibold">
                <i class="bi bi-journal-text me-1"></i> Log Riwayat Transaksi Peminjaman
            </span>
            <span class="badge bg-light text-dark border">
                Total: {{ $peminjamans->total() }} Transaksi
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-custom align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Kode Pinjam</th>
                        <th>Buku Dipinjam</th>
                        <th>Peminjam (Mahasiswa)</th>
                        <th>Tgl Pinjam &amp; Jatuh Tempo</th>
                        <th>Tgl Kembali</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peminjamans as $index => $item)
                        <tr>
                            <td class="text-center text-secondary small">
                                {{ $peminjamans->firstItem() + $index }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">
                                    {{ $item->kode_pinjam }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('buku.show', $item->buku_id) }}" class="fw-bold text-decoration-none text-dark d-block">
                                    {{ $item->buku->judul }}
                                </a>
                                <span class="badge bg-secondary-subtle text-secondary small">
                                    {{ $item->buku->kategori->nama_kategori ?? 'Umum' }}
                                </span>
                                <small class="text-muted font-monospace ms-1">ISBN: {{ $item->buku->isbn }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $item->nama_peminjam }}</div>
                                <div class="text-muted small font-monospace"><i class="bi bi-person-badge me-1"></i>{{ $item->nim_peminjam }}</div>
                            </td>
                            <td>
                                <div class="small text-dark">
                                    <i class="bi bi-calendar-event text-primary me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal_pinjam)->translatedFormat('d M Y') }}
                                </div>
                                <div class="small text-danger">
                                    <i class="bi bi-hourglass-split me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->translatedFormat('d M Y') }}
                                </div>
                            </td>
                            <td>
                                @if($item->tanggal_kembali)
                                    <span class="text-success small fw-medium">
                                        <i class="bi bi-check-circle me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal_kembali)->translatedFormat('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-muted small fst-italic">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->status === 'dipinjam')
                                    <span class="badge bg-warning text-dark px-2 py-1">
                                        <i class="bi bi-clock me-1"></i>Dipinjam
                                    </span>
                                @else
                                    <span class="badge bg-success px-2 py-1">
                                        <i class="bi bi-check2-circle me-1"></i>Dikembalikan
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($item->status === 'dipinjam')
                                    <form action="{{ route('peminjaman.kembali', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin buku ini telah dikembalikan oleh mahasiswa? Sisa stok buku akan dipulihkan secara atomik.');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-success btn-sm fw-medium shadow-sm">
                                            <i class="bi bi-box-arrow-in-down-left me-1"></i> Kembalikan
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-light btn-sm text-muted" disabled>
                                        <i class="bi bi-check-all me-1"></i> Selesai
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="py-3">
                                    <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                    <h6 class="fw-semibold text-secondary">Belum ada riwayat transaksi peminjaman buku</h6>
                                    <p class="text-muted small mb-3">Gunakan tombol di bawah untuk mencatat peminjaman baru mahasiswa.</p>
                                    <a href="{{ route('peminjaman.create') }}" class="btn btn-del btn-sm">
                                        <i class="bi bi-plus-circle me-1"></i> Catat Peminjaman Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($peminjamans->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                {{ $peminjamans->links() }}
            </div>
        @endif
    </div>
</x-layout>
