<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KriteriaPenilaian extends Model
{
    use HasFactory;

    protected $table = 'kriteria_penilaian';

    protected $fillable = [
        'kegiatan_id',
        'nama_aspek',
        'deskripsi',
        'bobot_persen',
        'urutan',
    ];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function penilaianDetail(): HasMany
    {
        return $this->hasMany(PenilaianDetail::class);
    }
}
