<?php

namespace Tests\Feature;

use App\Imports\PesertaTerpilihImport;
use App\Models\JadwalWawancara;
use App\Models\Kegiatan;
use App\Models\KriteriaPenilaian;
use App\Models\PesertaSeleksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class BusinessFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function seedKegiatan(): Kegiatan
    {
        $k = Kegiatan::create(['nama_kegiatan' => 'Rekrutmen 2027', 'tahun' => 2027, 'is_active' => true, 'pengumuman_dipublikasikan' => false]);
        foreach ([['Komunikasi',25,1],['Pengalaman Lapangan',25,2],['Penguasaan Teknologi / CAPI',25,3],['Komitmen & Integritas',25,4]] as $r) {
            KriteriaPenilaian::create(['kegiatan_id'=>$k->id,'nama_aspek'=>$r[0],'bobot_persen'=>$r[1],'urutan'=>$r[2]]);
        }
        return $k;
    }

    protected function mkUser(string $role, string $email, array $extra = []): User
    {
        return User::create(array_merge([
            'name' => $email,
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
            'must_change_password' => false,
            'is_active' => true,
        ], $extra));
    }

    public function test_peserta_seleksi_can_be_created_for_active_kegiatan(): void
    {
        $k = $this->seedKegiatan();
        $mitra = $this->mkUser('peserta', 'mitra@example.com', ['sobat_id' => 'S100']);
        Excel::fake();
        $import = new PesertaTerpilihImport($k);
        // simulasi via model (logika import)
        $ps = PesertaSeleksi::firstOrCreate(['kegiatan_id'=>$k->id,'user_id'=>$mitra->id], ['status_undangan'=>'diundang','status_hasil'=>'belum_ditentukan']);
        $this->assertDatabaseHas('peserta_seleksi', ['kegiatan_id'=>$k->id,'user_id'=>$mitra->id,'status_undangan'=>'diundang']);
        $this->assertTrue($ps->exists);
    }

    public function test_rsvp_and_cv_upload_and_scoring_and_pengumuman_flow(): void
    {
        Storage::fake('local');
        $k = $this->seedKegiatan();
        $peserta = $this->mkUser('peserta', 'peserta.flow@example.com', ['sobat_id' => 'SF1']);
        $pew = $this->mkUser('pewawancara', 'pew.flow@example.com');
        $ps = PesertaSeleksi::create(['kegiatan_id'=>$k->id,'user_id'=>$peserta->id,'status_undangan'=>'diundang','status_hasil'=>'belum_ditentukan']);

        // RSVP
        $this->actingAs($peserta)->post('/undangan/rsvp', ['status_undangan'=>'hadir'])->assertRedirect();
        $this->assertDatabaseHas('peserta_seleksi', ['id'=>$ps->id,'status_undangan'=>'hadir']);
        $this->assertNotNull($ps->fresh()->waktu_konfirmasi);

        // Upload CV PDF
        $file = UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');
        $this->actingAs($peserta)->post('/administrasi/upload', ['cv'=>$file])->assertRedirect();
        $ps->refresh();
        $this->assertNotNull($ps->cv_path);
        $this->assertStringStartsWith('private/cv/', $ps->cv_path);
        Storage::disk('local')->assertExists($ps->cv_path);
        $this->assertNotNull($ps->cv_uploaded_at);

        // Jadwal & scoring
        $jadwal = JadwalWawancara::create([
            'kegiatan_id'=>$k->id,'peserta_seleksi_id'=>$ps->id,'pewawancara_id'=>$pew->id,
            'tanggal'=>'2027-06-01','jam_mulai'=>'09:00:00','jam_selesai'=>'09:30:00','metode'=>'daring','lokasi_atau_link'=>'https://meet.example'
        ]);
        $kriteria = KriteriaPenilaian::where('kegiatan_id',$k->id)->orderBy('urutan')->get();
        $payload = [
            'rekomendasi' => 'sangat_direkomendasikan',
            'catatan' => 'Sangat baik',
            'skor' => [],
        ];
        $expected = 0.0;
        $scores = [80, 90, 70, 100];
        foreach ($kriteria as $i => $kr) {
            $s = $scores[$i];
            $payload['skor'][$kr->id] = $s;
            $expected += $s * ($kr->bobot_persen / 100);
        }
        $expected = round($expected, 2);

        $this->actingAs($pew)->post('/wawancara/'.$jadwal->id, $payload)->assertRedirect();
        $this->assertDatabaseHas('penilaian_wawancara', [
            'jadwal_wawancara_id'=>$jadwal->id,'pewawancara_id'=>$pew->id,'nilai_akhir'=>$expected,'rekomendasi'=>'sangat_direkomendasikan'
        ]);
        $pen = $jadwal->fresh()->penilaian;
        $this->assertEquals(4, $pen->details()->count());
        $this->assertEquals(85.00, (float) $pen->nilai_akhir);

        // Pengumuman: sebelum publikasi tertahan
        $resp = $this->actingAs($peserta)->get('/pengumuman');
        $resp->assertOk();
        $resp->assertSee('belum dipublikasikan');
        $resp->assertDontSee('LULUS');

        // Set hasil & publikasikan
        $ps->update(['status_hasil'=>'lulus','catatan_hasil'=>'Selamat lulus']);
        $k->update(['pengumuman_dipublikasikan'=>true]);

        $resp2 = $this->actingAs($peserta)->get('/pengumuman');
        $resp2->assertOk();
        $resp2->assertSee('LULUS');
        $resp2->assertSee('Selamat lulus');
        $resp2->assertDontSee('belum dipublikasikan');
    }
}
