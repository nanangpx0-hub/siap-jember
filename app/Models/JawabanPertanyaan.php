<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanPertanyaan extends Model
{
    use HasFactory;

    protected $table = 'jawaban_pertanyaan';

    protected $fillable = [
        'penilaian_wawancara_id',
        'pertanyaan_wawancara_id',
        'jawaban',
        'pilihan',
        'skor',
    ];

    public function penilaianWawancara(): BelongsTo
    {
        return $this->belongsTo(PenilaianWawancara::class);
    }

    public function pertanyaanWawancara(): BelongsTo
    {
        return $this->belongsTo(PertanyaanWawancara::class);
    }
}
