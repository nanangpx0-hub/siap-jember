<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JadwalWawancara extends Model
{
    use HasFactory;

    protected $table = 'jadwal_wawancara';

    protected $fillable = [
        'kegiatan_id',
        'peserta_seleksi_id',
        'pewawancara_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'sesi',
        'metode',
        'lokasi_atau_link',
        'catatan_jadwal',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function pesertaSeleksi(): BelongsTo
    {
        return $this->belongsTo(PesertaSeleksi::class);
    }

    public function pewawancara(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pewawancara_id');
    }

    public function penilaian(): HasOne
    {
        return $this->hasOne(PenilaianWawancara::class);
    }
}
