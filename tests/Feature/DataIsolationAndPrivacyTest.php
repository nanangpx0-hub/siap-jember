<?php

namespace Tests\Feature;

use App\Models\DokumenPenyelenggaraan;
use App\Models\JadwalWawancara;
use App\Models\Kegiatan;
use App\Models\KriteriaPenilaian;
use App\Models\PesertaSeleksi;
use App\Models\PertanyaanWawancara;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DataIsolationAndPrivacyTest extends TestCase
{
    use RefreshDatabase;

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

    protected function seedKegiatan(): Kegiatan
    {
        $k = Kegiatan::create(['nama_kegiatan' => 'Rekrutmen 2027', 'tahun' => 2027, 'is_active' => true, 'pengumuman_dipublikasikan' => false]);
        foreach ([['Komunikasi',25,1],['Pengalaman Lapangan',25,2],['Penguasaan Teknologi / CAPI',25,3],['Komitmen & Integritas',25,4]] as $r) {
            KriteriaPenilaian::create(['kegiatan_id'=>$k->id,'nama_aspek'=>$r[0],'bobot_persen'=>$r[1],'urutan'=>$r[2]]);
        }
        return $k;
    }

    public function test_peserta_cannot_access_other_participants_cv(): void
    {
        Storage::fake('local');
        $k = $this->seedKegiatan();
        $a = $this->mkUser('peserta', 'a@example.com', ['sobat_id' => 'A1']);
        $b = $this->mkUser('peserta', 'b@example.com', ['sobat_id' => 'B1']);
        $psA = PesertaSeleksi::create(['kegiatan_id'=>$k->id,'user_id'=>$a->id]);
        $psB = PesertaSeleksi::create(['kegiatan_id'=>$k->id,'user_id'=>$b->id]);
        $pathB = 'private/cv/'.$k->id.'/'.$b->id.'/cv.pdf';
        Storage::disk('local')->put($pathB, '%PDF-1.4');
        $psB->update(['cv_path'=>$pathB]);

        $this->actingAs($a)->get('/administrasi/cv/preview')->assertNotFound();
        $this->actingAs($a)->get('/administrasi/cv/download')->assertNotFound();

        // peserta B bisa akses miliknya sendiri
        $this->actingAs($b)->get('/administrasi/cv/preview')->assertOk();
        $this->actingAs($b)->get('/administrasi/cv/download')->assertOk();
    }

    public function test_pewawancara_cannot_access_other_interviewers_schedule_or_score_it(): void
    {
        Storage::fake('local');
        $k = $this->seedKegiatan();
        $pw1 = $this->mkUser('pewawancara', 'pw1@example.com');
        $pw2 = $this->mkUser('pewawancara', 'pw2@example.com');
        $pesU = $this->mkUser('peserta', 'pes@example.com');
        $ps = PesertaSeleksi::create(['kegiatan_id'=>$k->id,'user_id'=>$pesU->id]);
        $jadwalPw2 = JadwalWawancara::create([
            'kegiatan_id'=>$k->id,'peserta_seleksi_id'=>$ps->id,'pewawancara_id'=>$pw2->id,
            'tanggal'=>'2027-05-01','jam_mulai'=>'08:00:00','jam_selesai'=>'08:30:00','metode'=>'luring','lokasi_atau_link'=>'Ruang 1'
        ]);
        $cvPath = 'private/cv/'.$k->id.'/'.$pesU->id.'/cv.pdf';
        Storage::disk('local')->put($cvPath, '%PDF-1.4');
        $ps->update(['cv_path'=>$cvPath]);

        $this->actingAs($pw1)->get('/pewawancara/jadwal/'.$jadwalPw2->id)->assertForbidden();
        $this->actingAs($pw1)->get('/pewawancara/jadwal/'.$jadwalPw2->id.'/cv/preview')->assertForbidden();
        $this->actingAs($pw1)->get('/pewawancara/jadwal/'.$jadwalPw2->id.'/cv/download')->assertForbidden();
        $this->actingAs($pw1)->get('/wawancara/'.$jadwalPw2->id)->assertForbidden();
        $this->actingAs($pw1)->post('/wawancara/'.$jadwalPw2->id, [])->assertForbidden();

        // pemilik jadwal bisa akses
        $this->actingAs($pw2)->get('/pewawancara/jadwal/'.$jadwalPw2->id)->assertOk();
        $this->actingAs($pw2)->get('/wawancara/'.$jadwalPw2->id)->assertOk();
    }

    public function test_superadmin_can_score_any_jadwal_but_pewawancara_only_assigned(): void
    {
        $k = $this->seedKegiatan();
        $pw = $this->mkUser('pewawancara', 'pw-admin-test@example.com');
        $superadmin = $this->mkUser('superadmin', 'sadm@example.com');
        $op = $this->mkUser('pewawancara', 'pw-deny@example.com');
        $operator = $this->mkUser('operator', 'op-deny@example.com');
        $pesU = $this->mkUser('peserta', 'pes3@example.com');
        $ps = PesertaSeleksi::create(['kegiatan_id'=>$k->id,'user_id'=>$pesU->id]);
        $jadwal = JadwalWawancara::create([
            'kegiatan_id'=>$k->id,'peserta_seleksi_id'=>$ps->id,'pewawancara_id'=>$pw->id,
            'tanggal'=>'2027-05-02','jam_mulai'=>'09:00:00','jam_selesai'=>'09:30:00','metode'=>'luring','lokasi_atau_link'=>'Ruang 2'
        ]);

        // pewawancara non-pemilik & operator ditolak
        $this->actingAs($op)->get('/wawancara/'.$jadwal->id)->assertForbidden();
        $this->actingAs($op)->post('/wawancara/'.$jadwal->id, [])->assertForbidden();
        $this->actingAs($operator)->get('/wawancara/'.$jadwal->id)->assertForbidden();

        // superadmin dapat membuka form & menyimpan penilaian
        $this->actingAs($superadmin)->get('/wawancara/'.$jadwal->id)->assertOk();
        $payload = ['rekomendasi' => 'direkomendasikan', 'catatan' => 'Input oleh superadmin'];
        foreach (KriteriaPenilaian::where('kegiatan_id', $k->id)->get() as $kp) {
            $payload['skor'][$kp->id] = 80;
        }
        $this->actingAs($superadmin)->post('/wawancara/'.$jadwal->id, $payload)->assertRedirect();

        // pewawancara_id tetap milik jadwal (tidak ditimpa id superadmin)
        $this->assertDatabaseHas('penilaian_wawancara', [
            'jadwal_wawancara_id' => $jadwal->id,
            'pewawancara_id' => $pw->id,
            'nilai_akhir' => 80.00,
        ]);
    }

    public function test_per_question_answers_auto_compute_aspek_and_final_score(): void
    {
        $k = $this->seedKegiatan();
        $kriteria = KriteriaPenilaian::where('kegiatan_id', $k->id)->orderBy('urutan')->get();
        $kpKom = $kriteria[0]; // Komunikasi
        $kpPeng = $kriteria[1]; // Pengalaman Lapangan

        // Aspek 1 & 2 punya pertanyaan; aspek 3 & 4 tanpa pertanyaan (skor manual).
        $qBinier = PertanyaanWawancara::create([
            'kegiatan_id' => $k->id, 'kriteria_penilaian_id' => $kpKom->id,
            'teks' => '(Observasi) Apakah calon tampil rapi?', 'jenis' => 'binier', 'bobot' => 1, 'urutan' => 1,
            'pilihan' => [['teks' => 'YA', 'skor' => 100], ['teks' => 'TIDAK', 'skor' => 40]],
        ]);
        $qNaratif = PertanyaanWawancara::create([
            'kegiatan_id' => $k->id, 'kriteria_penilaian_id' => $kpPeng->id,
            'teks' => 'Ceritakan pengalaman survei Anda.', 'jenis' => 'naratif', 'bobot' => 2, 'urutan' => 1,
        ]);

        $pw = $this->mkUser('pewawancara', 'pw-q@example.com');
        $pesU = $this->mkUser('peserta', 'pesq@example.com');
        $ps = PesertaSeleksi::create(['kegiatan_id'=>$k->id,'user_id'=>$pesU->id]);
        $jadwal = JadwalWawancara::create([
            'kegiatan_id'=>$k->id,'peserta_seleksi_id'=>$ps->id,'pewawancara_id'=>$pw->id,
            'tanggal'=>'2027-05-03','jam_mulai'=>'10:00:00','jam_selesai'=>'10:30:00','metode'=>'luring','lokasi_atau_link'=>'Ruang 3'
        ]);

        $payload = [
            'rekomendasi' => 'direkomendasikan',
            'catatan' => 'Alur pertanyaan',
            'pilihan' => [$qBinier->id => 'TIDAK'],             // skor 40 (dari opsi)
            'skor_q' => [$qNaratif->id => 60],                  // skor manual naratif
            'skor' => [$kriteria[2]->id => 70, $kriteria[3]->id => 90], // aspek tanpa pertanyaan
        ];

        $this->actingAs($pw)->post('/wawancara/'.$jadwal->id, $payload)->assertRedirect();

        // skor aspek otomatis: Komunikasi = 40, Pengalaman = 60 (rata-rata tertimbang)
        $this->assertDatabaseHas('penilaian_detail', [
            'kriteria_penilaian_id' => $kpKom->id, 'skor' => 40,
        ]);
        $this->assertDatabaseHas('penilaian_detail', [
            'kriteria_penilaian_id' => $kpPeng->id, 'skor' => 60,
        ]);
        $this->assertDatabaseHas('penilaian_detail', [
            'kriteria_penilaian_id' => $kriteria[2]->id, 'skor' => 70,
        ]);
        $this->assertDatabaseHas('penilaian_detail', [
            'kriteria_penilaian_id' => $kriteria[3]->id, 'skor' => 90,
        ]);

        // nilai akhir berbobot = (40+60+70+90)/4 = 65.00
        $this->assertDatabaseHas('penilaian_wawancara', [
            'jadwal_wawancara_id' => $jadwal->id, 'pewawancara_id' => $pw->id, 'nilai_akhir' => 65.00,
        ]);

        // jawaban per pertanyaan tersimpan
        $this->assertDatabaseHas('jawaban_pertanyaan', [
            'pertanyaan_wawancara_id' => $qBinier->id, 'pilihan' => 'TIDAK', 'skor' => 40,
        ]);
        $this->assertDatabaseHas('jawaban_pertanyaan', [
            'pertanyaan_wawancara_id' => $qNaratif->id, 'skor' => 60,
        ]);
    }

    public function test_operator_documents_are_private_and_rejected_for_peserta(): void
    {
        Storage::fake('local');
        $k = $this->seedKegiatan();
        $op = $this->mkUser('operator', 'op@example.com');
        $peserta = $this->mkUser('peserta', 'pes2@example.com');
        $path = 'private/penyelenggaraan/'.$k->id.'/doc.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4');
        $doc = DokumenPenyelenggaraan::create([
            'kegiatan_id'=>$k->id,'kategori'=>'daftar_hadir','judul'=>'Daftar Hadir','file_path'=>$path,
            'original_filename'=>'doc.pdf','mime_type'=>'application/pdf','file_size'=>8,'uploaded_by'=>$op->id
        ]);

        $this->assertStringStartsWith('private/penyelenggaraan/', $doc->file_path);
        $this->assertTrue(Storage::disk('local')->exists($doc->file_path));

        $this->actingAs($peserta)->get('/operator/administrasi')->assertForbidden();
        $this->actingAs($peserta)->get('/operator/administrasi/'.$doc->id.'/download')->assertForbidden();
        $this->actingAs($op)->get('/operator/administrasi/'.$doc->id.'/download')->assertOk();
    }
}
