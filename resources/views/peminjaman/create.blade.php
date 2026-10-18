<x-layout title="Catat Peminjaman Buku Baru">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Navigasi Balik -->
            <div class="mb-3">
                <a href="{{ route('peminjaman.index') }}" class="text-decoration-none text-secondary small">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Sirkulasi
                </a>
            </div>

            <!-- Kartu Form Peminjaman -->
            <div class="card card-custom">
                <div class="card-header card-header-del py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-file-earmark-plus me-2"></i>Form Transaksi Peminjaman Buku
                    </h5>
                    <small class="text-muted">Operasi dibungkus dalam transaksi ACID &amp; Pessimistic Locking (lockForUpdate) untuk mencegah race condition stok.</small>
                </div>
                <div class="card-body p-4">
                    <!-- Info Box ACID Warning -->
                    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4" style="background-color: #EEF2FF; color: #3730A3;">
                        <i class="bi bi-shield-lock-fill fs-4 me-3"></i>
                        <div class="small">
                            <strong>Proteksi Konkurensi Tingkat Baris:</strong> Sistem akan mengunci baris buku yang bersangkutan selama transaksi berlangsung (<code>SELECT ... FOR UPDATE</code>) untuk menjamin stok buku tidak pernah bernilai negatif (-1).
                        </div>
                    </div>

                    <form action="{{ route('peminjaman.store') }}" method="POST">
                        @csrf

                        <!-- Pilihan Buku -->
                        <div class="mb-3">
                            <label for="buku_id" class="form-label fw-semibold">
                                Judul Buku yang Dipinjam <span class="text-danger">*</span>
                            </label>
                            <select name="buku_id" id="buku_id" class="form-select @error('buku_id') is-invalid @enderror" required>
                                <option value="" disabled {{ old('buku_id', $selectedBukuId) ? '' : 'selected' }}>-- Pilih Buku dari Katalog --</option>
                                @foreach($bukus as $b)
                                    <option value="{{ $b->id }}"
                                        {{ old('buku_id', $selectedBukuId) == $b->id ? 'selected' : '' }}
                                        {{ $b->stok < 1 ? 'class=text-danger' : '' }}>
                                        {{ $b->judul }} (ISBN: {{ $b->isbn }}) — Sisa Stok: {{ $b->stok }} eksemplar {{ $b->stok < 1 ? '[HABIS]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('buku_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">
                                Buku dengan stok 0 tidak dapat dipinjam dan akan otomatis ditolak oleh transaksi ACID.
                            </div>
                        </div>

                        <div class="row">
                            <!-- NIM Peminjam -->
                            <div class="col-md-5 mb-3">
                                <label for="nim_peminjam" class="form-label fw-semibold">
                                    NIM Mahasiswa <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="nim_peminjam"
                                       id="nim_peminjam"
                                       class="form-control font-monospace @error('nim_peminjam') is-invalid @enderror"
                                       value="{{ old('nim_peminjam', session('last_borrower_nim')) }}"
                                       placeholder="Contoh: 12S24002"
                                       maxlength="15"
                                       required>
                                @error('nim_peminjam')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if(session('last_borrower_nim'))
                                    <div class="form-text small text-primary">
                                        <i class="bi bi-clock-history me-1"></i> Disi dari memori sesi peminjam terakhir.
                                    </div>
                                @endif
                            </div>

                            <!-- Nama Peminjam -->
                            <div class="col-md-7 mb-3">
                                <label for="nama_peminjam" class="form-label fw-semibold">
                                    Nama Lengkap Mahasiswa <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="nama_peminjam"
                                       id="nama_peminjam"
                                       class="form-control @error('nama_peminjam') is-invalid @enderror"
                                       value="{{ old('nama_peminjam', session('last_borrower_name')) }}"
                                       placeholder="Contoh: Petra Ignatius Pengayoman Naibaho"
                                       maxlength="150"
                                       required>
                                @error('nama_peminjam')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Tanggal Pinjam -->
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_pinjam" class="form-label fw-semibold">
                                    Tanggal Peminjaman <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       name="tanggal_pinjam"
                                       id="tanggal_pinjam"
                                       class="form-control @error('tanggal_pinjam') is-invalid @enderror"
                                       value="{{ old('tanggal_pinjam', date('Y-m-d')) }}"
                                       required>
                                @error('tanggal_pinjam')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Durasi Peminjaman -->
                            <div class="col-md-6 mb-3">
                                <label for="durasi_hari" class="form-label fw-semibold">
                                    Durasi Pinjam (Hari)
                                </label>
                                <select name="durasi_hari" id="durasi_hari" class="form-select @error('durasi_hari') is-invalid @enderror">
                                    <option value="3" {{ old('durasi_hari') == 3 ? 'selected' : '' }}>3 Hari (Singkat)</option>
                                    <option value="7" {{ old('durasi_hari', 7) == 7 ? 'selected' : '' }}>7 Hari (Standar Perpustakaan Del)</option>
                                    <option value="14" {{ old('durasi_hari') == 14 ? 'selected' : '' }}>14 Hari (Dua Pekan)</option>
                                    <option value="21" {{ old('durasi_hari') == 21 ? 'selected' : '' }}>21 Hari (Tiga Pekan)</option>
                                </select>
                                @error('durasi_hari')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Tombol Aksi -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-del px-4">
                                <i class="bi bi-shield-check me-1"></i> Proses Peminjaman (Atomik)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
