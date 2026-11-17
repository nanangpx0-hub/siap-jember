<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_wawancara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_wawancara_id')->unique()->constrained('jadwal_wawancara')->cascadeOnDelete();
            $table->foreignId('pewawancara_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->enum('rekomendasi', ['sangat_direkomendasikan', 'direkomendasikan', 'dipertimbangkan', 'tidak_direkomendasikan'])->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_wawancara');
    }
};
