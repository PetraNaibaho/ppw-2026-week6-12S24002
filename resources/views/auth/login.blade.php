<x-layout title="Masuk Pustakawan">
    <div class="row justify-content-center py-4">
        <div class="col-md-6 col-lg-5">
            <!-- Header Brand Card -->
            <div class="card card-custom shadow-sm border-0">
                <div class="card-header text-center py-4 text-white" style="background: linear-gradient(135deg, #4C1D95 0%, #3B0764 100%); border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem;">
                    <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle bg-white bg-opacity-10 mb-2">
                        <i class="bi bi-shield-lock-fill text-warning fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Masuk Pustakawan</h4>
                    <p class="small text-white-50 mb-0">SIPUS-Del &bull; Institut Teknologi Del</p>
                </div>

                <div class="card-body p-4">
                    <!-- Akun Pengujian Alert -->
                    <div class="alert alert-warning border-0 small mb-4 py-2 px-3 shadow-none bg-warning-subtle text-warning-emphasis">
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-key-fill me-2 fs-5"></i>
                            <span class="fw-bold">Akun Pengujian Pustakawan Resmi:</span>
                        </div>
                        <ul class="mb-0 ps-3">
                            <li>Email: <code>pustakawan@del.ac.id</code></li>
                            <li>Password: <code>password123</code></li>
                        </ul>
                    </div>

                    <form action="{{ route('login') }}" method="POST">
                        @csrf

                        <!-- Email Input -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold small text-secondary">
                                Alamat Email Pustakawan <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope-at"></i></span>
                                <input type="email"
                                       name="email"
                                       id="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       placeholder="pustakawan@del.ac.id"
                                       value="{{ old('email', $rememberedEmail) }}"
                                       required
                                       autofocus>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @if($rememberedEmail)
                                <div class="form-text small text-primary">
                                    <i class="bi bi-cookie me-1"></i> Alamat email terisi otomatis dari HTTP Cookie browser.
                                </div>
                            @endif
                        </div>

                        <!-- Password Input -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold small text-secondary">
                                Kata Sandi <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                                <input type="password"
                                       name="password"
                                       id="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="••••••••••••"
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Remember Me Checkbox -->
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label small text-muted" for="remember">
                                    Ingat saya di peramban ini
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-del w-100 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem Pustakawan
                        </button>
                    </form>
                </div>

                <div class="card-footer bg-light border-0 py-3 text-center small text-muted" style="border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem;">
                    <a href="{{ route('buku.index') }}" class="text-decoration-none text-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog Publik
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout>
