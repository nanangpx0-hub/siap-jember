<?php

namespace App\Imports;

use App\Models\Kegiatan;
use App\Models\PesertaSeleksi;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PesertaTerpilihImport implements ToCollection, WithHeadingRow
{
    public array $summary = [
        'success' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    public function __construct(protected Kegiatan $kegiatan) {}

    public function collection(Collection $rows): void
    {
        $rowNum = 1;
        foreach ($rows as $row) {
            $rowNum++;
            $r = $row instanceof Collection ? $row->toArray() : (array) $row;
            $get = function (array $keys) use ($r) {
                foreach ($keys as $k) {
                    if (array_key_exists($k, $r) && $r[$k] !== null && $r[$k] !== '') {
                        $value = is_string($r[$k]) ? trim($r[$k]) : (string) $r[$k];
                        if ($value !== '') {
                            return $value;
                        }
                    }
                }
                return '';
            };

            // Cukup Email. Kolom SOBAT ID (jika masih ada di file lama) diabaikan.
            $email = strtolower($get(['email', 'email_peserta', 'alamat_email']));

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: Email kosong atau tidak valid.";
                continue;
            }

            $user = User::where('email', $email)->first();

            if (! $user) {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: mitra dengan email {$email} tidak ditemukan.";
                continue;
            }

            $ps = PesertaSeleksi::firstOrCreate(
                ['kegiatan_id' => $this->kegiatan->id, 'user_id' => $user->id],
                ['status_undangan' => 'diundang', 'status_hasil' => 'belum_ditentukan']
            );

            if ($ps->wasRecentlyCreated) {
                $this->summary['success']++;
            } else {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: peserta sudah terdaftar pada kegiatan aktif ({$user->email}).";
            }
        }
    }
}
