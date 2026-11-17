<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@bpsjember.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'must_change_password' => false,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'operator@bpsjember.com'],
            [
                'name' => 'Operator',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'must_change_password' => false,
                'is_active' => true,
            ]
        );

        $kegiatan = Kegiatan::updateOrCreate(
            ['nama_kegiatan' => 'Rekrutmen Keputusan Kepala BPS Tahun 2027', 'tahun' => 2027],
            [
                'deskripsi' => 'Kegiatan seleksi rekrutmen Keputusan Kepala BPS Tahun 2027 Kabupaten Jember.',
                'is_active' => true,
                'pengumuman_dipublikasikan' => false,
            ]
        );

        // Kriteria (4 aspek) + bank pertanyaan (17) default dikelola terpusat di model Kegiatan,
        // sehingga kegiatan baru dari UI (/tugas) otomatis mendapat konfigurasi yang sama.
        $kegiatan->seedDefaultKriteriaDanPertanyaan();
    }
}
