<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_pertanyaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_wawancara_id')->constrained('penilaian_wawancara')->cascadeOnDelete();
            $table->foreignId('pertanyaan_wawancara_id')->constrained('pertanyaan_wawancara')->cascadeOnDelete();
            $table->text('jawaban')->nullable(); // jawaban naratif (teks bebas)
            $table->string('pilihan')->nullable(); // teks opsi terpilih untuk binier/kategori
            $table->unsignedTinyInteger('skor'); // skor pertanyaan 0-100
            $table->timestamps();
            // Nama eksplisit pendek: nama otomatis Laravel >64 karakter dan gagal di MySQL 8.
            $table->unique(['penilaian_wawancara_id', 'pertanyaan_wawancara_id'], 'jawaban_pertanyaan_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_pertanyaan');
    }
};
