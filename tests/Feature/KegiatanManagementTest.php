<?php

namespace Tests\Feature;

use App\Models\Kegiatan;
use App\Models\KriteriaPenilaian;
use App\Models\PertanyaanWawancara;
use App\Models\TugasPanitia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KegiatanManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $role.'@example.com',
            'password' => Hash::make('password123'),
            'role' => $role,
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }

    protected function kegiatan(string $nama, bool $aktif = false): Kegiatan
    {
        return Kegiatan::create([
            'nama_kegiatan' => $nama,
            'tahun' => 2027,
            'is_active' => $aktif,
            'pengumuman_dipublikasikan' => false,
        ]);
    }

    public function test_operator_dapat_menambah_kegiatan_dan_menjadikannya_aktif(): void
    {
        $lama = $this->kegiatan('Kegiatan Lama', true);
        $operator = $this->user('operator');

        $this->actingAs($operator)->post('/tugas/kegiatan', [
            'nama_kegiatan' => 'Kegiatan Baru 2028',
            'tahun' => 2028,
            'deskripsi' => 'Seleksi baru',
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('kegiatan', [
            'nama_kegiatan' => 'Kegiatan Baru 2028', 'tahun' => 2028, 'is_active' => true,
        ]);
        $this->assertFalse($lama->fresh()->is_active, 'Kegiatan lama harus dinonaktifkan.');

        $baru = Kegiatan::where('nama_kegiatan', 'Kegiatan Baru 2028')->firstOrFail();
        $this->assertSame(4, KriteriaPenilaian::where('kegiatan_id', $baru->id)->count());
        $this->assertSame(17, PertanyaanWawancara::where('kegiatan_id', $baru->id)->count());
        foreach (KriteriaPenilaian::where('kegiatan_id', $baru->id)->get() as $kriteria) {
            $this->assertSame(25, (int) $kriteria->bobot_persen);
        }

        // Belum ada data operasional => kriteria & pertanyaan ikut terhapus via cascade.
        $this->actingAs($operator)->delete('/tugas/kegiatan/'.$baru->id)->assertRedirect();
        $this->assertDatabaseMissing('kegiatan', ['id' => $baru->id]);
        $this->assertSame(0, KriteriaPenilaian::where('kegiatan_id', $baru->id)->count());
        $this->assertSame(0, PertanyaanWawancara::where('kegiatan_id', $baru->id)->count());
    }

    public function test_operator_dapat_mengaktifkan_kegiatan_nonaktif(): void
    {
        $operator = $this->user('operator');
        $aktif = $this->kegiatan('Kegiatan Aktif', true);
        $nonaktif = $this->kegiatan('Kegiatan Nonaktif', false);

        $this->actingAs($operator)->post('/tugas/kegiatan/'.$nonaktif->id.'/activate')->assertRedirect();

        $this->assertTrue((bool) $nonaktif->fresh()->is_active);
        $this->assertFalse((bool) $aktif->fresh()->is_active, 'Kegiatan lain harus dinonaktifkan.');
    }

    public function test_kegiatan_kosong_dapat_dihapus(): void
    {
        $operator = $this->user('operator');
        $kegiatan = $this->kegiatan('Kegiatan Kosong');

        $this->actingAs($operator)->delete('/tugas/kegiatan/'.$kegiatan->id)->assertRedirect();

        $this->assertDatabaseMissing('kegiatan', ['id' => $kegiatan->id]);
    }

    public function test_kegiatan_berisi_data_tidak_bisa_dihapus(): void
    {
        $operator = $this->user('operator');
        $kegiatan = $this->kegiatan('Kegiatan Berisi');
        $petugas = $this->user('pewawancara');
        TugasPanitia::create([
            'kegiatan_id' => $kegiatan->id,
            'user_id' => $petugas->id,
            'peran_tugas' => 'Pewawancara',
        ]);

        $this->actingAs($operator)->delete('/tugas/kegiatan/'.$kegiatan->id)
            ->assertSessionHasErrors('kegiatan');

        $this->assertDatabaseHas('kegiatan', ['id' => $kegiatan->id]);
    }

    public function test_pewawancara_tidak_bisa_menambah_atau_menghapus_kegiatan(): void
    {
        $pewawancara = $this->user('pewawancara');
        $kegiatan = $this->kegiatan('Kegiatan Terkunci');

        $this->actingAs($pewawancara)->post('/tugas/kegiatan', [
            'nama_kegiatan' => 'Tidak boleh', 'tahun' => 2028,
        ])->assertForbidden();

        $this->actingAs($pewawancara)->delete('/tugas/kegiatan/'.$kegiatan->id)->assertForbidden();
    }
}
