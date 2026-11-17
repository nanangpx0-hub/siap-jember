<?php

namespace Tests\Feature;

use App\Imports\JadwalWawancaraImport;
use App\Models\JadwalWawancara;
use App\Models\Kegiatan;
use App\Models\PesertaSeleksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JadwalManagementAndImportTest extends TestCase
{
    use RefreshDatabase;

    protected function user(string $role, string $email): User
    {
        return User::create([
            'name' => $email,
            'email' => $email,
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

    protected function baris(string $emailPeserta, string $emailPew, string $lokasi): array
    {
        return [
            'email_peserta' => $emailPeserta,
            'email_pewawancara' => $emailPew,
            'tanggal' => '2027-08-01',
            'jam_mulai' => '09:00',
            'jam_selesai' => '09:30',
            'sesi' => '1',
            'metode' => 'luring',
            'lokasi_atau_link' => $lokasi,
        ];
    }

    public function test_import_jadwal_idempoten_dan_mendeteksi_duplikat_dalam_file(): void
    {
        $k = $this->kegiatan('Kegiatan Aktif', true);
        $pew = $this->user('pewawancara', 'pew@jadwal.example.com');
        $peserta = $this->user('peserta', 'peserta@jadwal.example.com');
        $ps = PesertaSeleksi::create(['kegiatan_id' => $k->id, 'user_id' => $peserta->id]);

        $import1 = new JadwalWawancaraImport($k);
        $import1->collection(collect([$this->baris($peserta->email, $pew->email, 'Ruang A')]));
        $this->assertSame(1, $import1->summary['success']);
        $this->assertSame(0, $import1->summary['updated']);
        $this->assertSame(1, JadwalWawancara::where('peserta_seleksi_id', $ps->id)->count());

        // Import ulang file yang sama -> diperbarui, bukan duplikat.
        $import2 = new JadwalWawancaraImport($k);
        $import2->collection(collect([$this->baris($peserta->email, $pew->email, 'Ruang B')]));
        $this->assertSame(0, $import2->summary['success']);
        $this->assertSame(1, $import2->summary['updated']);
        $this->assertSame(1, JadwalWawancara::where('peserta_seleksi_id', $ps->id)->count());
        $this->assertSame('Ruang B', JadwalWawancara::first()->lokasi_atau_link);

        // Duplikat peserta dalam satu file -> baris kedua dilewati.
        $baris = $this->baris($peserta->email, $pew->email, 'Ruang C');
        $import3 = new JadwalWawancaraImport($k);
        $import3->collection(collect([$baris, $baris]));
        $this->assertSame(0, $import3->summary['success']);
        $this->assertSame(1, $import3->summary['updated']);
        $this->assertSame(1, $import3->summary['skipped']);
        $this->assertStringContainsString('duplikat peserta', $import3->summary['errors'][0]);
        $this->assertSame(1, JadwalWawancara::where('peserta_seleksi_id', $ps->id)->count());
    }

    public function test_input_manual_jadwal_menolak_peserta_dari_kegiatan_lain_dan_jam_terbalik(): void
    {
        $operator = $this->user('operator', 'operator@jadwal.example.com');
        $pew = $this->user('pewawancara', 'pew2@jadwal.example.com');
        $kA = $this->kegiatan('Kegiatan A');
        $kB = $this->kegiatan('Kegiatan B');
        $psA = PesertaSeleksi::create(['kegiatan_id' => $kA->id, 'user_id' => $this->user('peserta', 'a@jadwal.example.com')->id]);
        $psB = PesertaSeleksi::create(['kegiatan_id' => $kB->id, 'user_id' => $this->user('peserta', 'b@jadwal.example.com')->id]);

        $base = [
            'kegiatan_id' => $kA->id,
            'peserta_seleksi_id' => $psA->id,
            'pewawancara_id' => $pew->id,
            'tanggal' => '2027-08-02',
            'jam_mulai' => '09:00',
            'jam_selesai' => '09:30',
            'metode' => 'luring',
            'lokasi_atau_link' => 'Ruang 1',
        ];

        $this->actingAs($operator)->post('/jadwal', array_merge($base, ['peserta_seleksi_id' => $psB->id]))
            ->assertSessionHasErrors('peserta_seleksi_id');

        $this->actingAs($operator)->post('/jadwal', array_merge($base, [
            'jam_mulai' => '10:00', 'jam_selesai' => '09:30',
        ]))->assertSessionHasErrors('jam_selesai');

        $this->actingAs($operator)->post('/jadwal', array_merge($base, ['pewawancara_id' => $psA->user_id]))
            ->assertSessionHasErrors('pewawancara_id');

        $this->assertSame(0, JadwalWawancara::count());
    }

    public function test_input_manual_jadwal_idempoten(): void
    {
        $operator = $this->user('operator', 'operator2@jadwal.example.com');
        $pew = $this->user('pewawancara', 'pew3@jadwal.example.com');
        $k = $this->kegiatan('Kegiatan C');
        $ps = PesertaSeleksi::create(['kegiatan_id' => $k->id, 'user_id' => $this->user('peserta', 'c@jadwal.example.com')->id]);

        $payload = [
            'kegiatan_id' => $k->id,
            'peserta_seleksi_id' => $ps->id,
            'pewawancara_id' => $pew->id,
            'tanggal' => '2027-08-03',
            'jam_mulai' => '08:00',
            'jam_selesai' => '08:30',
            'metode' => 'daring',
            'lokasi_atau_link' => 'https://meet.example/1',
        ];

        $this->actingAs($operator)->post('/jadwal', $payload)->assertRedirect();
        $this->actingAs($operator)->post('/jadwal', array_merge($payload, ['lokasi_atau_link' => 'https://meet.example/2']))->assertRedirect();

        $this->assertSame(1, JadwalWawancara::count());
        $this->assertSame('https://meet.example/2', JadwalWawancara::first()->lokasi_atau_link);
    }
}
