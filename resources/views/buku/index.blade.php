<x-layout>
    <x-slot:title>Katalog Koleksi Buku</x-slot:title>

    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-collection-fill text-primary" style="color: #4C1D95 !important;"></i>
                Katalog Koleksi Buku
            </h2>
            <p class="text-muted mb-0 small">
                Manajemen data sirkulasi, inventarisasi, dan analitik koleksi perpustakaan Institut Teknologi Del.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-del btn-sm">
                <i class="bi bi-arrow-left-right me-1"></i> Sirkulasi Peminjaman
            </a>
            <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-tags me-1"></i> Kelola Kategori
            </a>
            <a href="{{ route('buku.create') }}" class="btn btn-del btn-sm shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Buku Baru
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards (Fluent Query Builder DB::table) -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Judul Buku -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 border-start border-4" style="border-left-color: #4C1D95 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Total Judul Buku</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['total_judul'] ?? $totalBuku) }}</h3>
                    </div>
                    <div class="p-3 rounded-circle badge-del fs-4">
                        <i class="bi bi-book"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Stok Tersedia -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Total Stok Tersedia</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['total_stok'] ?? $totalStok) }}</h3>
                    </div>
                    <div class="p-3 rounded-circle bg-success-subtle text-success fs-4">
                        <i class="bi bi-stack"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total Sedang Dipinjam -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Buku Sedang Dipinjam</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['total_dipinjam'] ?? 0) }}</h3>
                    </div>
                    <div class="p-3 rounded-circle bg-warning-subtle text-warning-emphasis fs-4">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Total Buku Selesai Dikembalikan -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Total Dikembalikan</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['total_dikembalikan'] ?? 0) }}</h3>
                    </div>
                    <div class="p-3 rounded-circle bg-info-subtle text-info fs-4">
                        <i class="bi bi-check2-all"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card with Dynamic when() -->
    <div class="card card-custom mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('buku.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan judul, penulis, penerbit, atau ISBN..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="kategori_id" class="form-select form-select-sm">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                [{{ $kat->kode_kategori }}] {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-del btn-sm flex-fill">
                        <i class="bi bi-funnel-fill me-1"></i> Filter
                    </button>
                    @if(request('search') || request('kategori_id'))
                        <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset filter">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
            @if(session('last_book_search'))
                <div class="text-muted mt-2" style="font-size: 0.75rem;">
                    <i class="bi bi-clock-history me-1"></i> Kata kunci pencarian sesi terakhir: <code>{{ session('last_book_search') }}</code>
                </div>
            @endif
        </div>
    </div>

    <!-- Table of Books with withCount('peminjamans') -->
    <div class="card card-custom">
        <div class="card-header card-header-del d-flex justify-content-between align-items-center py-3">
            <span class="fw-semibold">
                <i class="bi bi-table me-1"></i> Daftar Koleksi Buku
            </span>
            <span class="badge bg-secondary-subtle text-secondary small">
                Menampilkan {{ $bukus->total() }} entri buku
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th style="width: 130px;">ISBN</th>
                        <th>Judul Buku &amp; Kategori</th>
                        <th>Penulis &amp; Penerbit</th>
                        <th class="text-center" style="width: 70px;">Tahun</th>
                        <th class="text-center" style="width: 90px;">Stok</th>
                        <th class="text-center" style="width: 90px;">Dipinjam</th>
                        <th class="text-center" style="width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bukus as $buku)
                        <tr>
                            <td class="text-center text-muted fw-medium small">
                                {{ $bukus->firstItem() ? $bukus->firstItem() + $loop->index : $loop->iteration }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace border small">
                                    {{ $buku->isbn }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    <a href="{{ route('buku.show', $buku) }}" class="text-decoration-none text-dark hover-purple">
                                        {{ $buku->judul }}
                                    </a>
                                </div>
                                <div class="mt-1">
                                    <a href="{{ route('kategori.show', $buku->kategori) }}" class="badge badge-del text-decoration-none small">
                                        <i class="bi bi-tag-fill me-1"></i>{{ $buku->kategori->nama_kategori }}
                                    </a>
                                </div>
                            </td>
                            <td class="small">
                                <div class="fw-medium text-dark">{{ $buku->penulis }}</div>
                                <div class="text-muted"><i class="bi bi-building me-1"></i>{{ $buku->penerbit }}</div>
                            </td>
                            <td class="text-center small text-secondary">
                                {{ $buku->tahun_terbit }}
                            </td>
                            <td class="text-center">
                                @if($buku->stok > 10)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        {{ $buku->stok }} eks
                                    </span>
                                @elseif($buku->stok > 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                        {{ $buku->stok }} eks
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        Habis
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border small font-monospace">
                                    <i class="bi bi-arrow-repeat text-primary me-1"></i>{{ $buku->peminjamans_count }}x
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <!-- Quick Borrow Button -->
                                    @if($buku->stok > 0)
                                        <a href="{{ route('peminjaman.create', ['buku_id' => $buku->id]) }}" class="btn btn-outline-success" title="Pinjam Buku">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @else
                                        <button class="btn btn-light text-danger" disabled title="Stok Habis">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                    @endif

                                    <a href="{{ route('buku.show', $buku) }}" class="btn btn-outline-primary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('buku.edit', $buku) }}" class="btn btn-outline-warning" title="Ubah Data">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('buku.destroy', $buku) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku \'{{ addslashes($buku->judul) }}\'?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus Buku">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-folder-x fs-1 text-secondary opacity-50"></i>
                                    <h6 class="mt-3 fw-bold">Belum Ada Data Buku</h6>
                                    <p class="small text-muted mb-3">
                                        @if(request('search') || request('kategori_id'))
                                            Tidak ditemukan buku yang sesuai dengan kriteria pencarian Anda.
                                        @else
                                            Belum ada koleksi buku yang terdaftar di perpustakaan.
                                        @endif
                                    </p>
                                    @if(request('search') || request('kategori_id'))
                                        <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Tampilkan Semua Buku
                                        </a>
                                    @else
                                        <a href="{{ route('buku.create') }}" class="btn btn-del btn-sm">
                                            <i class="bi bi-plus-lg me-1"></i> Tambah Buku Pertama
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($bukus->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                {{ $bukus->links() }}
            </div>
        @endif
    </div>
</x-layout>
