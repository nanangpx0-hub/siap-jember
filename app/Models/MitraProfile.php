<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MitraProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'posisi',
        'status_seleksi_2026',
        'posisi_daftar',
        'alamat_detail',
        'alamat_provinsi',
        'alamat_kab_kota',
        'alamat_kecamatan',
        'alamat_desa_kel',
        'ttl',
        'umur',
        'jenis_kelamin',
        'status_kawin',
        'pendidikan',
        'pekerjaan',
        'terikat_kontrak_kldi',
        'nama_kldi',
        'punya_motor',
        'bisa_motor',
        'berpengalaman_capi',
        'punya_android',
        'merk_hp',
        'tipe_hp',
        'ram_hp',
        'bisa_komputer',
        'punya_laptop',
        'pernah_pendataan_bps',
        'pernah_sp',
        'pernah_st',
        'pernah_se',
        'pernah_susenas',
        'pernah_sakernas',
        'pernah_sbh',
        'status_nik',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
