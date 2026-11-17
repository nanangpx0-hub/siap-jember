<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_wawancara_id')->constrained('penilaian_wawancara')->cascadeOnDelete();
            $table->foreignId('kriteria_penilaian_id')->constrained('kriteria_penilaian')->cascadeOnDelete();
            $table->unsignedTinyInteger('skor');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_detail');
    }
};
