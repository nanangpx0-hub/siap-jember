<?php

namespace Tests\Feature;

use App\Imports\MitraMasterImport;
use App\Models\DokumenPenyelenggaraan;
use App\Models\JadwalWawancara;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OperationalSecurityAndPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function user(string $role, string $email): User
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

    protected function kegiatan(bool $aktif = true): Kegiatan
    {
        return Kegiatan::create([
            'nama_kegiatan' => 'Rekrutmen 2027',
            'tahun' => 2027,
            'is_active' => $aktif,
            'pengumuman_dipublikasikan' => false,
        ]);
    }

    public function test_upload_berkas_penyelenggaraan_menolak_ekstensi_berbahaya(): void
    {
        Storage::fake('local');
        $operator = $this->user('operator', 'operator@ops.example.com');
        $k = $this->kegiatan();

        foreach (['berkas.exe', 'berkas.php'] as $nama) {
            $this->actingAs($operator)->post('/operator/administrasi', [
                'kegiatan_id' => $k->id,
                'kategori' => 'lainnya',
                'judul' => $nama,
                'file' => UploadedFile::fake()->create($nama, 32),
            ])->assertSessionHasErrors('file');
        }

        $this->assertSame(0, DokumenPenyelenggaraan::count());
    }

    public function test_upload_berkas_penyelenggaraan_menerima_pdf(): void
    {
        Storage::fake('local');
        $operator = $this->user('operator', 'operator2@ops.example.com');
        $k = $this->kegiatan();

        $this->actingAs($operator)->post('/operator/administrasi', [
            'kegiatan_id' => $k->id,
            'kategori' => 'daftar_hadir',
            'judul' => 'Daftar Hadir',
            'file' => UploadedFile::fake()->create('daftar-hadir.pdf', 32, 'application/pdf'),
        ])->assertRedirect();

        $doc = DokumenPenyelenggaraan::firstOrFail();
        $this->assertSame('daftar-hadir.pdf', $doc->original_filename);
        Storage::disk('local')->assertExists($doc->file_path);
    }

    public function test_export_rekap_hanya_untuk_superadmin_dan_operator(): void
    {
        $this->kegiatan();
        $operator = $this->user('operator', 'operator3@ops.example.com');
        $superadmin = $this->user('superadmin', 'superadmin@ops.example.com');
        $pewawancara = $this->user('pewawancara', 'pew@ops.example.com');
        $peserta = $this->user('peserta', 'peserta@ops.example.com');

        $this->actingAs($operator)->get('/operator/pengumuman/export-rekap')->assertOk();
        $this->actingAs($superadmin)->get('/operator/pengumuman/export-rekap')->assertOk();
        $this->actingAs($pewawancara)->get('/operator/pengumuman/export-rekap')->assertForbidden();
        $this->actingAs($peserta)->get('/operator/pengumuman/export-rekap')->assertForbidden();
    }

    public function test_mitra_master_import_optimasi_tetap_akurat(): void
    {
        $lama = User::create([
            'name' => 'Lama',
            'email' => 'lama@mitra.example.com',
            'password' => Hash::make('password-lama'),
            'role' => 'peserta',
            'must_change_password' => true,
            'is_active' => true,
        ]);
        $pemilik = User::create([
            'name' => 'Pemilik',
            'email' => 'pemilik@mitra.example.com',
            'sobat_id' => 'SBC',
            'password' => Hash::make('password123'),
            'role' => 'peserta',
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $import = new MitraMasterImport();
        $import->collection(collect([
            ['email' => 'baru@mitra.example.com', 'sobat_id' => 'SB1', 'nama_lengkap' => 'Mitra Baru', 'alamat_kecamatan' => 'Sumbersari'],
            ['email' => 'lama@mitra.example.com', 'sobat_id' => 'SB2', 'nama_lengkap' => 'Lama Diubah'],
            ['email' => 'lain@mitra.example.com', 'sobat_id' => 'SBC', 'nama_lengkap' => 'Konflik'],
        ]));

        $this->assertSame(1, $import->summary['success']);
        $this->assertSame(1, $import->summary['updated']);
        $this->assertSame(1, $import->summary['skipped']);
        $this->assertStringContainsString('sudah dipakai user lain', $import->summary['errors'][0]);

        $baru = User::where('email', 'baru@mitra.example.com')->firstOrFail();
        $this->assertSame('peserta', $baru->role);
        $this->assertTrue((bool) $baru->must_change_password);
        $this->assertTrue(Hash::check('SB1', $baru->password));
        $this->assertSame('Sumbersari', $baru->mitraProfile->alamat_kecamatan);

        $lama->refresh();
        $this->assertSame('Lama Diubah', $lama->name);
        $this->assertSame('SB2', $lama->sobat_id);
        $this->assertTrue(Hash::check('password-lama', $lama->password), 'Password existing tidak boleh tertimpa.');
        $this->assertTrue((bool) $lama->must_change_password);

        $this->assertNull(User::where('email', 'lain@mitra.example.com')->first());
        $this->assertSame('Pemilik', $pemilik->name);
    }

    public function test_index_pengumuman_operator_dengan_kegiatan_aktif_tidak_error(): void
    {
        $operator = $this->user('operator', 'operator@pengumuman.example.com');
        $k = $this->kegiatan();
        $peserta = User::create([
            'name' => 'Peserta Pengumuman',
            'email' => 'pengumuman@mitra.example.com',
            'sobat_id' => 'SPENG',
            'password' => Hash::make('password123'),
            'role' => 'peserta',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        \App\Models\PesertaSeleksi::create([
            'kegiatan_id' => $k->id,
            'user_id' => $peserta->id,
            'status_hasil' => 'lulus',
        ]);

        $response = $this->actingAs($operator)->get('/operator/pengumuman')->assertOk();
        $response->assertSee('Peserta Pengumuman');
        $response->assertSee('Export Rekap Nilai');

        $this->actingAs($operator)->get('/operator/pengumuman?q=PENG&status_hasil=lulus')
            ->assertOk()->assertSee('Peserta Pengumuman');
    }

    public function test_rekap_export_memiliki_kolom_kompatibel_dan_data_benar(): void
    {
        $k = $this->kegiatan();
        $peserta = User::create([
            'name' => 'Peserta Rekap',
            'email' => 'rekap@mitra.example.com',
            'sobat_id' => 'S123',
            'password' => Hash::make('password123'),
            'role' => 'peserta',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        \App\Models\PesertaSeleksi::create([
            'kegiatan_id' => $k->id,
            'user_id' => $peserta->id,
            'status_undangan' => 'hadir',
            'status_hasil' => 'lulus',
            'catatan_hasil' => 'Direkomendasikan',
        ]);

        $export = new \App\Exports\RekapNilaiExport($k->id);
        $this->assertCount(12, $export->headings());
        $this->assertContains('sobat_id', $export->headings());
        $this->assertContains('status_hasil', $export->headings());
        $this->assertContains('catatan_hasil', $export->headings());

        $rows = $export->array();
        $this->assertCount(1, $rows);
        $this->assertSame('S123', $rows[0][0]);
        $this->assertSame('rekap@mitra.example.com', $rows[0][1]);
        $this->assertSame('Peserta Rekap', $rows[0][2]);
        $this->assertSame('hadir', $rows[0][4]);
        $this->assertSame('Tidak', $rows[0][5]);
        $this->assertSame('lulus', $rows[0][10]);
        $this->assertSame('Direkomendasikan', $rows[0][11]);
    }

    public function test_dashboard_operator_menampilkan_statistik_kegiatan_aktif(): void
    {
        $operator = $this->user('superadmin', 'superadmin@dash.example.com');
        $k = $this->kegiatan();
        $pesertaUser = $this->user('peserta', 'peserta@dash.example.com');
        $pew = $this->user('pewawancara', 'pew@dash.example.com');
        $ps = \App\Models\PesertaSeleksi::create([
            'kegiatan_id' => $k->id,
            'user_id' => $pesertaUser->id,
            'status_undangan' => 'hadir',
            'cv_path' => 'private/cv/'.$k->id.'/'.$pesertaUser->id.'/cv.pdf',
        ]);
        $jadwal = JadwalWawancara::create([
            'kegiatan_id' => $k->id,
            'peserta_seleksi_id' => $ps->id,
            'pewawancara_id' => $pew->id,
            'tanggal' => '2027-08-01',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '09:30:00',
            'metode' => 'luring',
            'lokasi_atau_link' => 'Ruang 1',
        ]);
        \App\Models\PenilaianWawancara::create([
            'jadwal_wawancara_id' => $jadwal->id,
            'pewawancara_id' => $pew->id,
            'nilai_akhir' => 85.00,
        ]);

        $response = $this->actingAs($operator)->get('/operator/dashboard')->assertOk();
        $response->assertSee('Rekrutmen 2027');
        $response->assertSee('Total Mitra');
        $response->assertSee('Peserta Terpilih');
        $response->assertSee('RSVP Hadir');
        $response->assertSee('Upload CV');
        $response->assertSee('Sudah Dinilai');
    }
}
