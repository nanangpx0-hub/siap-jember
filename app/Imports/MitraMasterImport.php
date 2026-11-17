<?php

namespace App\Imports;

use App\Models\MitraProfile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MitraMasterImport implements ToCollection, WithHeadingRow
{
    public array $summary = [
        'success' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    /** @var array<string,int> */
    protected array $seenEmails = [];
    /** @var array<string,int> */
    protected array $seenSobat = [];

    public function collection(Collection $rows): void
    {
        $rowNum = 1; // heading
        foreach ($rows as $row) {
            $rowNum++;
            $data = $this->normalizeRow($row->toArray());
            $email = $data['email'];
            $sobatId = $data['sobat_id'];
            $name = $data['name'] ?: ($sobatId ?: 'Mitra');

            if ($email === '') {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: email kosong (SOBAT ID: {$sobatId}).";
                continue;
            }
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: email tidak valid ({$email}).";
                continue;
            }

            $emailKey = strtolower($email);
            if (isset($this->seenEmails[$emailKey])) {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: duplikat email dalam file ({$email}).";
                continue;
            }
            $this->seenEmails[$emailKey] = $rowNum;

            if ($sobatId !== '') {
                if (isset($this->seenSobat[$sobatId])) {
                    $this->summary['skipped']++;
                    $this->summary['errors'][] = "Baris {$rowNum}: duplikat SOBAT ID dalam file ({$sobatId}).";
                    continue;
                }
                $this->seenSobat[$sobatId] = $rowNum;
            }

            $existingByEmail = User::where('email', $email)->first();
            $existingBySobat = $sobatId !== '' ? User::where('sobat_id', $sobatId)->where('email', '!=', $email)->first() : null;
            if ($existingBySobat) {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: SOBAT ID {$sobatId} sudah dipakai user lain.";
                continue;
            }

            $isNew = ! $existingByEmail;
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role' => $existingByEmail ? $existingByEmail->role : 'peserta',
                    'sobat_id' => $sobatId !== '' ? $sobatId : ($existingByEmail->sobat_id ?? null),
                    'no_telp' => $data['no_telp'] !== '' ? $data['no_telp'] : ($existingByEmail->no_telp ?? null),
                    'password' => $existingByEmail ? $existingByEmail->password : Hash::make($sobatId !== '' ? $sobatId : 'password'),
                    'must_change_password' => $isNew ? true : $existingByEmail->must_change_password,
                    'is_active' => $existingByEmail ? $existingByEmail->is_active : true,
                ]
            );

            $profileData = $data['profile'];
            $profileData['user_id'] = $user->id;
            MitraProfile::updateOrCreate(['user_id' => $user->id], $profileData);

            if ($isNew) {
                $this->summary['success']++;
            } else {
                $this->summary['updated']++;
            }
        }
    }

    protected function normalizeRow(array $row): array
    {
        $get = function (array $keys) use ($row) {
            foreach ($keys as $k) {
                if (array_key_exists($k, $row) && $row[$k] !== null && $row[$k] !== '') {
                    return is_string($row[$k]) ? trim($row[$k]) : (string) $row[$k];
                }
            }
            return '';
        };

        $email = strtolower($get(['email']));
        $sobat = $get(['sobat_id', 'sobatid', 'sobat']);
        $name = $get(['nama_lengkap', 'nama']);
        $noTelp = $get(['no_telp', 'no_hp', 'no_telepon', 'telepon']);

        $profile = [
            'posisi' => $get(['posisi']) ?: null,
            'status_seleksi_2026' => $get(['status_seleksi', 'status_seleksi_2026']) ?: null,
            'posisi_daftar' => $get(['posisi_daftar']) ?: null,
            'alamat_detail' => $get(['alamat_detail']) ?: null,
            'alamat_provinsi' => $get(['alamat_provinsi']) ?: null,
            'alamat_kab_kota' => $get(['alamat_kab_kota', 'alamat_kabkota']) ?: null,
            'alamat_kecamatan' => $get(['alamat_kecamatan']) ?: null,
            'alamat_desa_kel' => $get(['alamat_desa_kel', 'alamat_desakel']) ?: null,
            'ttl' => $get(['tempat_tanggal_lahir', 'ttl']) ?: null,
            'umur' => $get(['umur']) ?: null,
            'jenis_kelamin' => $get(['jenis_kelamin']) ?: null,
            'status_kawin' => $get(['status_kawin']) ?: null,
            'pendidikan' => $get(['pendidikan']) ?: null,
            'pekerjaan' => $get(['pekerjaan']) ?: null,
            'terikat_kontrak_kldi' => $get(['terikat_kontrak_kldi_lain', 'terikat_kontrak_kldi']) ?: null,
            'nama_kldi' => $get(['nama_kldi']) ?: null,
            'punya_motor' => $get(['punya_kendaraan_bermotor', 'punya_motor']) ?: null,
            'bisa_motor' => $get(['bisa_mengendarai_motor', 'bisa_motor']) ?: null,
            'berpengalaman_capi' => $get(['berpengalaman_capi']) ?: null,
            'punya_android' => $get(['punya_hp_android', 'punya_android']) ?: null,
            'merk_hp' => $get(['merk_hp']) ?: null,
            'tipe_hp' => $get(['tipe_hp']) ?: null,
            'ram_hp' => $get(['ram_hp']) ?: null,
            'bisa_komputer' => $get(['bisa_komputer']) ?: null,
            'punya_laptop' => $get(['punya_laptop']) ?: null,
            'pernah_pendataan_bps' => $get(['pernah_pendataan_bps']) ?: null,
            'pernah_sp' => $get(['pernah_sp']) ?: null,
            'pernah_st' => $get(['pernah_st']) ?: null,
            'pernah_se' => $get(['pernah_se']) ?: null,
            'pernah_susenas' => $get(['pernah_susenas']) ?: null,
            'pernah_sakernas' => $get(['pernah_sakernas']) ?: null,
            'pernah_sbh' => $get(['pernah_sbh']) ?: null,
            'status_nik' => $get(['status_nik']) ?: null,
        ];

        return [
            'name' => $name,
            'email' => $email,
            'sobat_id' => $sobat,
            'no_telp' => $noTelp,
            'profile' => $profile,
        ];
    }
}
