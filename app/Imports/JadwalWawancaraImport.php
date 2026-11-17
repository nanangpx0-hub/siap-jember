<?php

namespace App\Imports;

use App\Models\JadwalWawancara;
use App\Models\Kegiatan;
use App\Models\PesertaSeleksi;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JadwalWawancaraImport implements ToCollection, WithHeadingRow
{
    public array $summary = ['success' => 0, 'skipped' => 0, 'errors' => []];

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
                        $v = $r[$k];
                        return is_string($v) ? trim($v) : $v;
                    }
                }
                return '';
            };

            $emailPeserta = strtolower((string) $get(['email_peserta', 'email']));
            $sobatPeserta = (string) $get(['sobat_id_peserta', 'sobat_id']);
            $emailPew = strtolower((string) $get(['email_pewawancara', 'pewawancara_email']));
            $tanggal = $get(['tanggal']);
            $jamMulai = $get(['jam_mulai']);
            $jamSelesai = $get(['jam_selesai']);
            $metode = strtolower((string) $get(['metode'])) ?: 'luring';
            $lokasi = (string) $get(['lokasi_atau_link', 'lokasi', 'link']);

            $pesertaUser = null;
            if ($sobatPeserta !== '') $pesertaUser = User::where('sobat_id', $sobatPeserta)->first();
            if (!$pesertaUser && $emailPeserta !== '') $pesertaUser = User::where('email', $emailPeserta)->first();
            $peserta = $pesertaUser ? PesertaSeleksi::where('kegiatan_id', $this->kegiatan->id)->where('user_id', $pesertaUser->id)->first() : null;
            $pew = $emailPew !== '' ? User::where('email', $emailPew)->where('role', 'pewawancara')->first() : null;

            if (!$peserta || !$pew || !$tanggal || !$jamMulai || !$jamSelesai || !$lokasi) {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: data tidak lengkap/valid (peserta terpilih, pewawancara, tanggal, jam, lokasi wajib).";
                continue;
            }
            if (!in_array($metode, ['luring','daring'], true)) $metode = 'luring';

            try {
                if (is_numeric($tanggal)) {
                    $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggal)->format('Y-m-d');
                } else {
                    $tanggal = date('Y-m-d', strtotime((string)$tanggal));
                }
                $jm = is_numeric($jamMulai) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($jamMulai)->format('H:i:s') : date('H:i:s', strtotime((string)$jamMulai));
                $js = is_numeric($jamSelesai) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($jamSelesai)->format('H:i:s') : date('H:i:s', strtotime((string)$jamSelesai));
            } catch (\Throwable $e) {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: format tanggal/jam tidak valid.";
                continue;
            }

            JadwalWawancara::create([
                'kegiatan_id' => $this->kegiatan->id,
                'peserta_seleksi_id' => $peserta->id,
                'pewawancara_id' => $pew->id,
                'tanggal' => $tanggal,
                'jam_mulai' => $jm,
                'jam_selesai' => $js,
                'sesi' => (string) $get(['sesi']) ?: null,
                'metode' => $metode,
                'lokasi_atau_link' => $lokasi,
                'catatan_jadwal' => (string) $get(['catatan_jadwal', 'catatan']) ?: null,
            ]);
            $this->summary['success']++;
        }
    }
}
