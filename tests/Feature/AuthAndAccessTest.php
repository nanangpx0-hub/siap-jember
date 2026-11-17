<?php

namespace Tests\Feature;

use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_route_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }

    public function test_login_with_email_works_and_must_change_password_redirects(): void
    {
        $user = User::create([
            'name' => 'Peserta Uji',
            'email' => 'peserta.uji@example.com',
            'password' => Hash::make('password123'),
            'role' => 'peserta',
            'must_change_password' => true,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'peserta.uji@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('password.change.form'));
        $this->assertAuthenticatedAs($user);

        $this->get('/peserta/dashboard')->assertRedirect(route('password.change.form'));
    }

    public function test_url_intended_redirects_to_target_after_login(): void
    {
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Keg 2027', 'tahun' => 2027, 'is_active' => true]);
        $user = User::create([
            'name' => 'Peserta Intended',
            'email' => 'intended@example.com',
            'password' => Hash::make('password123'),
            'role' => 'peserta',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $user->pesertaSeleksi()->create(['kegiatan_id' => $kegiatan->id, 'status_undangan' => 'diundang', 'status_hasil' => 'belum_ditentukan']);

        $this->get('/undangan')->assertRedirect(route('login'));
        $response = $this->post('/login', [
            'email' => 'intended@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('http://localhost/undangan');
    }
}
