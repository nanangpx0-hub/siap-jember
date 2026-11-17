<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pertanyaan_wawancara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->cascadeOnDelete();
            $table->foreignId('kriteria_penilaian_id')->constrained('kriteria_penilaian')->cascadeOnDelete();
            $table->text('teks');
            $table->enum('jenis', ['binier', 'kategori', 'naratif']);
            $table->json('pilihan')->nullable(); // [{"teks":"YA","skor":100},{"teks":"TIDAK","skor":50}] untuk binier/kategori
            $table->unsignedTinyInteger('bobot')->default(1);
            $table->unsignedTinyInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pertanyaan_wawancara');
    }
};
