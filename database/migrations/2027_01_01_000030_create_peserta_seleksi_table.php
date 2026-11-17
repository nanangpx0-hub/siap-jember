<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peserta_seleksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status_undangan', ['diundang', 'hadir', 'tidak_hadir'])->default('diundang');
            $table->timestamp('waktu_konfirmasi')->nullable();
            $table->string('cv_path')->nullable();
            $table->timestamp('cv_uploaded_at')->nullable();
            $table->enum('status_hasil', ['belum_ditentukan', 'lulus', 'tidak_lulus', 'cadangan'])->default('belum_ditentukan');
            $table->text('catatan_hasil')->nullable();
            $table->timestamps();
            $table->unique(['kegiatan_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta_seleksi');
    }
};
