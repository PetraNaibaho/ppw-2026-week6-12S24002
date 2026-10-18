<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan formulir autentikasi masuk (login) pustakawan.
     */
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('buku.index');
        }

        // Ambil riwayat email pustakawan dari HTTP Cookie aman (jika ada)
        $rememberedEmail = $request->cookie('del_last_user_email');

        return view('auth.login', compact('rememberedEmail'));
    }

    /**
     * Autentikasi kredensial pustakawan, regenerasi ID sesi (anti-Session Fixation),
     * catat session state waktu login, dan terbitkan HTTP Cookie.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            // WAJIB: Regenerasi Session ID untuk menangkal serangan Session Fixation
            $request->session()->regenerate();

            // Catat state sesi: waktu login presisi
            session(['login_timestamp' => now()->format('d M Y, H:i:s') . ' WIB']);

            // Terbitkan HTTP Cookie aman (HttpOnly, masa aktif 7 hari = 10080 menit)
            Cookie::queue('del_last_user_email', $request->email, 10080);

            return redirect()->intended(route('buku.index'))
                ->with('success', 'Autentikasi berhasil. Sesi pustakawan Anda kini telah aktif.');
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->with('error', 'Kredensial login pustakawan tidak cocok dengan catatan kami.');
    }

    /**
     * Hapus autentikasi pustakawan, invalidate sesi, dan regenerasi token CSRF.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Bersihkan dan hancurkan data sesi di server
        $request->session()->invalidate();

        // Regenerasi token CSRF untuk mencegah serangan form reuse
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sesi pustakawan pada server telah ditutup dan dihapus secara aman.');
    }
}
