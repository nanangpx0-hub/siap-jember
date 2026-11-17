<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mitra_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('posisi')->nullable();
            $table->string('status_seleksi_2026')->nullable();
            $table->string('posisi_daftar')->nullable();
            $table->text('alamat_detail')->nullable();
            $table->string('alamat_provinsi')->nullable();
            $table->string('alamat_kab_kota')->nullable();
            $table->string('alamat_kecamatan')->nullable();
            $table->string('alamat_desa_kel')->nullable();
            $table->string('ttl')->nullable();
            $table->string('umur')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('status_kawin')->nullable();
            $table->string('pendidikan')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('terikat_kontrak_kldi')->nullable();
            $table->string('nama_kldi')->nullable();
            $table->string('punya_motor')->nullable();
            $table->string('bisa_motor')->nullable();
            $table->string('berpengalaman_capi')->nullable();
            $table->string('punya_android')->nullable();
            $table->string('merk_hp')->nullable();
            $table->string('tipe_hp')->nullable();
            $table->string('ram_hp')->nullable();
            $table->string('bisa_komputer')->nullable();
            $table->string('punya_laptop')->nullable();
            $table->string('pernah_pendataan_bps')->nullable();
            $table->string('pernah_sp')->nullable();
            $table->string('pernah_st')->nullable();
            $table->string('pernah_se')->nullable();
            $table->string('pernah_susenas')->nullable();
            $table->string('pernah_sakernas')->nullable();
            $table->string('pernah_sbh')->nullable();
            $table->string('status_nik')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mitra_profiles');
    }
};
