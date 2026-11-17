<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TugasPanitia extends Model
{
    use HasFactory;

    protected $table = 'tugas_panitia';

    protected $fillable = [
        'kegiatan_id',
        'user_id',
        'peran_tugas',
        'wilayah_atau_ruang',
        'keterangan',
    ];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
