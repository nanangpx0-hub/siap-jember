<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenilaianWawancara extends Model
{
    use HasFactory;

    protected $table = 'penilaian_wawancara';

    protected $fillable = [
        'jadwal_wawancara_id',
        'pewawancara_id',
        'nilai_akhir',
        'rekomendasi',
        'catatan',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'nilai_akhir' => 'decimal:2',
            'submitted_at' => 'datetime',
        ];
    }

    public function jadwalWawancara(): BelongsTo
    {
        return $this->belongsTo(JadwalWawancara::class);
    }

    public function pewawancara(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pewawancara_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PenilaianDetail::class);
    }

    public function jawabanPertanyaan(): HasMany
    {
        return $this->hasMany(JawabanPertanyaan::class);
    }
}
