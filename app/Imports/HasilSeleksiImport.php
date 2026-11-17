<?php

namespace App\Imports;

use App\Models\Kegiatan;
use App\Models\PesertaSeleksi;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HasilSeleksiImport implements ToCollection, WithHeadingRow
{
    public array $summary = ['success' => 0, 'skipped' => 0, 'errors' => []];

    public function __construct(protected Kegiatan $kegiatan) {}

    public function collection(Collection $rows): void
    {
        $rowNum = 1;
        foreach ($rows as $row) {
            $rowNum++;
            $r = $row->toArray();
            $get = function(array $keys) use ($r) {
                foreach ($keys as $k) {
                    if (array_key_exists($k,$r) && $r[$k] !== null && $r[$k] !== '') {
                        return is_string($r[$k]) ? trim($r[$k]) : (string)$r[$k];
                    }
                }
                return '';
            };
            $sobat = $get(['sobat_id','sobatid']);
            $email = strtolower($get(['email']));
            $status = strtolower($get(['status_hasil','status']));
            $catatan = $get(['catatan_hasil','catatan']);

            if (!in_array($status, ['lulus','tidak_lulus','cadangan','belum_ditentukan'], true)) {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: status_hasil tidak valid.";
                continue;
            }
            $user = null;
            if ($sobat !== '') $user = User::where('sobat_id',$sobat)->first();
            if (!$user && $email !== '') $user = User::where('email',$email)->first();
            $peserta = $user ? PesertaSeleksi::where('kegiatan_id',$this->kegiatan->id)->where('user_id',$user->id)->first() : null;
            if (!$peserta) {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: peserta seleksi tidak ditemukan pada kegiatan aktif.";
                continue;
            }
            $peserta->update(['status_hasil'=>$status,'catatan_hasil'=>$catatan ?: null]);
            $this->summary['success']++;
        }
    }
}
