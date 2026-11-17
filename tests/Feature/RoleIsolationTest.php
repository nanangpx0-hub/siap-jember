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

    public function test_peserta_cannot_access_operator_admin_or_pewawancara_endpoints(): void
    {
        $peserta = $this->makeUser('peserta', 'peserta@example.com');

        $forbidden = [
            '/operator/dashboard',
            '/operator/mitra',
            '/operator/administrasi',
            '/operator/pengumuman',
            '/pewawancara/dashboard',
        ];
        foreach ($forbidden as $url) {
            $this->actingAs($peserta)->get($url)->assertForbidden();
        }

        // Akses kontrak /administrasi dan /pengumuman untuk peserta diarahkan ke tampilan peserta (200), bukan panel operator
        $this->actingAs($peserta)->get('/administrasi')->assertOk();
        $this->actingAs($peserta)->get('/pengumuman')->assertOk();
    }

    public function test_pewawancara_cannot_access_operator_admin_endpoints(): void
    {
        $pew = $this->makeUser('pewawancara', 'pew@example.com');

        $forbidden = [
            '/operator/dashboard',
            '/operator/mitra',
            '/operator/administrasi',
            '/operator/pengumuman',
        ];
        foreach ($forbidden as $url) {
            $this->actingAs($pew)->get($url)->assertForbidden();
        }

        // /jadwal dan /tugas boleh untuk pewawancara (view), /administrasi & /pengumuman kontrak ditolak
        $this->actingAs($pew)->get('/jadwal')->assertOk();
        $this->actingAs($pew)->get('/tugas')->assertOk();
        $this->actingAs($pew)->get('/administrasi')->assertForbidden();
        $this->actingAs($pew)->get('/pengumuman')->assertForbidden();
    }
}
