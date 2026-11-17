<?php

namespace Tests\Feature;

use App\Imports\PesertaTerpilihImport;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PesertaTerpilihImportTest extends TestCase
{
    use RefreshDatabase;

    protected function kegiatan(): Kegiatan
    {
        return Kegiatan::create([
            'nama_kegiatan' => 'Rekrutmen 2027',
            'tahun' => 2027,
            'is_active' => true,
            'pengumuman_dipublikasikan' => false,
        ]);
    }

    protected function mitra(string $email): User
    {
        return User::create([
            'name' => $email,
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'peserta',
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }

    public function test_import_peserta_terpilih_cukup_dengan_email(): void
    {
        $kegiatan = $this->kegiatan();
        $a = $this->mitra('a@example.com');
        $b = $this->mitra('b@example.com');

        $import = new PesertaTerpilihImport($kegiatan);
        $import->collection(collect([
            ['email' => 'A@EXAMPLE.COM'],          // dinormalisasi lowercase
            ['email' => ' b@example.com '],        // dipangkas spasi
            ['email' => ''],                       // kosong -> dilewati
            ['email' => 'bukan-email'],            // tidak valid -> dilewati
            ['email' => 'tidakada@example.com'],   // tidak ditemukan -> dilewati
            ['email' => 'a@example.com'],          // duplikat kegiatan -> dilewati
        ]));

        $this->assertSame(2, $import->summary['success']);
        $this->assertSame(4, $import->summary['skipped']);
        $this->assertDatabaseHas('peserta_seleksi', [
            'kegiatan_id' => $kegiatan->id, 'user_id' => $a->id, 'status_undangan' => 'diundang',
        ]);
        $this->assertDatabaseHas('peserta_seleksi', [
            'kegiatan_id' => $kegiatan->id, 'user_id' => $b->id, 'status_undangan' => 'diundang',
        ]);
        $this->assertCount(4, $import->summary['errors']);
    }

    public function test_kolom_sobat_id_diabaikan_dan_email_tetap_jadi_kunci(): void
    {
        $kegiatan = $this->kegiatan();
        $a = $this->mitra('a@example.com');

        $import = new PesertaTerpilihImport($kegiatan);
        $import->collection(collect([
            ['sobat_id' => 'SOBAT_SALAH', 'email' => 'a@example.com'],
            ['sobat_id' => 'SOBAT_ADA', 'email' => ''],
        ]));

        $this->assertSame(1, $import->summary['success']);
        $this->assertSame(1, $import->summary['skipped']);
        $this->assertDatabaseHas('peserta_seleksi', [
            'kegiatan_id' => $kegiatan->id, 'user_id' => $a->id,
        ]);
    }
}
