<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'sobat_id',
        'no_telp',
        'must_change_password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function mitraProfile(): HasOne
    {
        return $this->hasOne(MitraProfile::class);
    }

    public function pesertaSeleksi(): HasMany
    {
        return $this->hasMany(PesertaSeleksi::class);
    }

    public function tugasPanitia(): HasMany
    {
        return $this->hasMany(TugasPanitia::class);
    }

    public function jadwalSebagaiPewawancara(): HasMany
    {
        return $this->hasMany(JadwalWawancara::class, 'pewawancara_id');
    }

    public function penilaianWawancara(): HasMany
    {
        return $this->hasMany(PenilaianWawancara::class, 'pewawancara_id');
    }

    public function dokumenPenyelenggaraanUploaded(): HasMany
    {
        return $this->hasMany(DokumenPenyelenggaraan::class, 'uploaded_by');
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];
        return in_array($this->role, $roles, true);
    }
}

