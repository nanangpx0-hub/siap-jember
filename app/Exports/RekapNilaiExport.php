<?php

namespace App\Exports;

use App\Models\PesertaSeleksi;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RekapNilaiExport implements FromArray, WithHeadings
{
    public function __construct(protected int $kegiatanId) {}

    public function headings(): array
    {
        return [
            'sobat_id',
            'email',
            'nama_peserta',
            'kecamatan',
            'status_undangan',
            'sudah_upload_cv',
            'pewawancara',
            'nilai_akhir',
            'rekomendasi',
            'catatan_wawancara',
            'status_hasil',
            'catatan_hasil',
        ];
    }

    public function array(): array
    {
        $peserta = PesertaSeleksi::with([
            'user.mitraProfile',
            'jadwalWawancara.penilaian',
            'jadwalWawancara.pewawancara',
        ])->where('kegiatan_id', $this->kegiatanId)->get()
            ->sortBy(fn ($p) => $p->user->name ?? '');

        return $peserta->map(function ($p) {
            $penilaian = $p->jadwalWawancara->map->penilaian->filter()->sortByDesc('submitted_at')->first();

            return [
                $p->user->sobat_id ?? '',
                $p->user->email ?? '',
                $p->user->name ?? '',
                $p->user->mitraProfile->alamat_kecamatan ?? '',
                $p->status_undangan ?? '',
                $p->cv_path ? 'Ya' : 'Tidak',
                optional(optional($p->jadwalWawancara->first())->pewawancara)->name ?? '',
                $penilaian->nilai_akhir ?? '',
                $penilaian->rekomendasi ?? '',
                $penilaian->catatan ?? '',
                $p->status_hasil ?? '',
                $p->catatan_hasil ?? '',
            ];
        })->values()->all();
    }
}
