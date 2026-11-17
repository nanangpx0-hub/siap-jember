<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianDetail extends Model
{
    use HasFactory;

    protected $table = 'penilaian_detail';

    protected $fillable = [
        'penilaian_wawancara_id',
        'kriteria_penilaian_id',
        'skor',
    ];

    public function penilaianWawancara(): BelongsTo
    {
        return $this->belongsTo(PenilaianWawancara::class);
    }

    public function kriteriaPenilaian(): BelongsTo
    {
        return $this->belongsTo(KriteriaPenilaian::class);
    }
}
