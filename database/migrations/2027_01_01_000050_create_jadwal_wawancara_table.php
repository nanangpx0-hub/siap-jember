<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_wawancara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->cascadeOnDelete();
            $table->foreignId('peserta_seleksi_id')->constrained('peserta_seleksi')->cascadeOnDelete();
            $table->foreignId('pewawancara_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('sesi')->nullable();
            $table->enum('metode', ['luring', 'daring']);
            $table->string('lokasi_atau_link');
            $table->text('catatan_jadwal')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_wawancara');
    }
};
