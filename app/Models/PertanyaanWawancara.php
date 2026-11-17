<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PertanyaanWawancara extends Model
{
    use HasFactory;

    protected $table = 'pertanyaan_wawancara';

    protected $fillable = [
        'kegiatan_id',
        'kriteria_penilaian_id',
        'teks',
        'jenis',
        'pilihan',
        'bobot',
        'urutan',
    ];

    protected $casts = [
        'pilihan' => 'array',
    ];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function kriteriaPenilaian(): BelongsTo
    {
        return $this->belongsTo(KriteriaPenilaian::class);
    }

    public function jawabanPertanyaan(): HasMany
    {
        return $this->hasMany(JawabanPertanyaan::class);
    }
}
