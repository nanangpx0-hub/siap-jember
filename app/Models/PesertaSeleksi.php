<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PesertaSeleksi extends Model
{
    use HasFactory;

    protected $table = 'peserta_seleksi';

    protected $fillable = [
        'kegiatan_id',
        'user_id',
        'status_undangan',
        'waktu_konfirmasi',
        'cv_path',
        'cv_uploaded_at',
        'status_hasil',
        'catatan_hasil',
    ];

    protected function casts(): array
    {
        return [
            'waktu_konfirmasi' => 'datetime',
            'cv_uploaded_at' => 'datetime',
        ];
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jadwalWawancara(): HasMany
    {
        return $this->hasMany(JadwalWawancara::class);
    }
}
