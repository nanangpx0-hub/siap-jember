<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role, string $email): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }

    public function test_peserta_cannot_access_staff_endpoints(): void
    {
        $peserta = $this->makeUser('peserta', 'peserta@example.com');

        $forbidden = [
            '/operator/dashboard',
            '/operator/mitra',
            '/operator/administrasi',
            '/operator/pengumuman',
            '/pewawancara/dashboard',
            '/tugas',
        ];
        foreach ($forbidden as $url) {
            $this->actingAs($peserta)->get($url)->assertForbidden();
        }

        // Akses kontrak /administrasi dan /pengumuman untuk peserta tetap ke tampilan peserta (200).
        $this->actingAs($peserta)->get('/administrasi')->assertOk();
        $this->actingAs($peserta)->get('/pengumuman')->assertOk();
    }

    public function test_operator_can_manage_but_cannot_access_penilaian_or_peserta_portal(): void
    {
        $operator = $this->makeUser('operator', 'operator@example.com');

        foreach ([
            '/operator/dashboard', '/operator/mitra', '/operator/administrasi',
            '/operator/pengumuman', '/tugas', '/jadwal',
        ] as $url) {
            $this->actingAs($operator)->get($url)->assertOk();
        }

        $this->actingAs($operator)->get('/pewawancara/dashboard')->assertForbidden();
        $this->actingAs($operator)->get('/peserta/dashboard')->assertForbidden();
        $this->actingAs($operator)->get('/undangan')->assertForbidden();
    }

    public function test_pewawancara_can_view_tugas_jadwal_and_penilaian(): void
    {
        $pewawancara = $this->makeUser('pewawancara', 'pew@example.com');

        foreach (['/tugas', '/jadwal', '/pewawancara/dashboard'] as $url) {
            $this->actingAs($pewawancara)->get($url)->assertOk();
        }

        foreach (['/operator/dashboard', '/operator/mitra', '/operator/administrasi', '/operator/pengumuman'] as $url) {
            $this->actingAs($pewawancara)->get($url)->assertForbidden();
        }
    }

    public function test_superadmin_has_full_staff_access(): void
    {
        $superadmin = $this->makeUser('superadmin', 'superadmin@example.com');

        foreach ([
            '/operator/dashboard', '/operator/mitra', '/operator/administrasi',
            '/operator/pengumuman', '/tugas', '/jadwal', '/pewawancara/dashboard',
        ] as $url) {
            $this->actingAs($superadmin)->get($url)->assertOk();
        }
    }
}
