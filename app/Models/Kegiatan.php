<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    use HasFactory;

    protected $table = 'kegiatan';

    protected $fillable = [
        'nama_kegiatan',
        'tahun',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'pengumuman_dipublikasikan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'is_active' => 'boolean',
            'pengumuman_dipublikasikan' => 'boolean',
        ];
    }

    public function pesertaSeleksi(): HasMany
    {
        return $this->hasMany(PesertaSeleksi::class);
    }

    public function tugasPanitia(): HasMany
    {
        return $this->hasMany(TugasPanitia::class);
    }

    public function jadwalWawancara(): HasMany
    {
        return $this->hasMany(JadwalWawancara::class);
    }

    public function kriteriaPenilaian(): HasMany
    {
        return $this->hasMany(KriteriaPenilaian::class);
    }

    public function dokumenPenyelenggaraan(): HasMany
    {
        return $this->hasMany(DokumenPenyelenggaraan::class);
    }
}
