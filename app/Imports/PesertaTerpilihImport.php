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
            $r = $row->toArray();
            $get = function (array $keys) use ($r) {
                foreach ($keys as $k) {
                    if (array_key_exists($k, $r) && $r[$k] !== null && $r[$k] !== '') {
                        return is_string($r[$k]) ? trim($r[$k]) : (string) $r[$k];
                    }
                }
                return '';
            };
            $sobat = $get(['sobat_id', 'sobatid', 'sobat']);
            $email = strtolower($get(['email']));

            $user = null;
            if ($sobat !== '') {
                $user = User::where('sobat_id', $sobat)->first();
            }
            if (! $user && $email !== '') {
                $user = User::where('email', $email)->first();
            }

            if (! $user) {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: mitra tidak ditemukan (SOBAT ID: {$sobat}, Email: {$email}).";
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
