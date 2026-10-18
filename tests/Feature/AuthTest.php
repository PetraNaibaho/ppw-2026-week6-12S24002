<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Pustakawan Institut Teknologi Del',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);
    }

    /**
     * Uji halaman form login dapat dimuat dengan baik.
     */
    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Masuk Pustakawan');
        $response->assertSee('pustakawan@del.ac.id');
    }

    /**
     * Uji autentikasi berhasil, Session ID diregenerasi (anti-Session Fixation),
     * session state waktu login tercatat, dan HTTP Cookie diterbitkan.
     */
    public function test_librarian_can_login_with_valid_credentials_and_regenerate_session(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('buku.index'));
        $this->assertAuthenticatedAs($this->user);

        // Verifikasi session state login timestamp tersimpan
        $response->assertSessionHas('login_timestamp');

        // Verifikasi HTTP Cookie del_last_user_email diterbitkan ke antrean cookie
        $response->assertCookie('del_last_user_email', 'pustakawan@del.ac.id');
    }

    /**
     * Uji penolakan autentikasi saat kredensial kata sandi salah.
     */
    public function test_librarian_cannot_login_with_invalid_password(): void
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'passwordsalah',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    /**
     * Uji alur logout aman: sesi di-invalidate dan dialihkan ke login.
     */
    public function test_librarian_can_logout_and_session_is_invalidated(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Uji proteksi middleware auth: tamu (guest) ditolak dan diarahkan ke login
     * ketika mencoba mengakses rute mutasi buku dan sirkulasi peminjaman.
     */
    public function test_guest_is_redirected_to_login_on_protected_routes(): void
    {
        // Akses form tambah buku tanpa login
        $this->get(route('buku.create'))->assertRedirect(route('login'));

        // Akses halaman sirkulasi peminjaman tanpa login
        $this->get(route('peminjaman.index'))->assertRedirect(route('login'));

        // Akses form sirkulasi peminjaman baru tanpa login
        $this->get(route('peminjaman.create'))->assertRedirect(route('login'));

        // Akses form tambah kategori tanpa login
        $this->get(route('kategori.create'))->assertRedirect(route('login'));
    }

    /**
     * Uji pustakawan terautentikasi dapat mengakses rute terlindungi.
     */
    public function test_authenticated_librarian_can_access_protected_routes(): void
    {
        $this->actingAs($this->user);

        $this->get(route('buku.create'))->assertStatus(200);
        $this->get(route('peminjaman.index'))->assertStatus(200);
        $this->get(route('peminjaman.create'))->assertStatus(200);
        $this->get(route('kategori.create'))->assertStatus(200);
    }
}
