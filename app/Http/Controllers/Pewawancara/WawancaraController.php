<?php

namespace App\Http\Controllers\Pewawancara;

use App\Http\Controllers\Controller;
use App\Models\JadwalWawancara;
use App\Models\JawabanPertanyaan;
use App\Models\KriteriaPenilaian;
use App\Models\PenilaianDetail;
use App\Models\PenilaianWawancara;
use App\Models\PertanyaanWawancara;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WawancaraController extends Controller
{
    public function edit(Request $request, JadwalWawancara $jadwal): View
    {
        $this->ensureOwner($request, $jadwal);
        $jadwal->load(['kegiatan', 'pesertaSeleksi.user', 'penilaian.details']);

        $kriteria = KriteriaPenilaian::where('kegiatan_id', $jadwal->kegiatan_id)
            ->orderBy('urutan')
            ->get();

        $penilaian = $jadwal->penilaian;
        $existing = $penilaian ? $penilaian->details->keyBy('kriteria_penilaian_id') : collect();

        // Bank pertanyaan per kriteria; kegiatan tanpa pertanyaan memakai input skor manual per aspek.
        $pertanyaan = PertanyaanWawancara::where('kegiatan_id', $jadwal->kegiatan_id)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->groupBy('kriteria_penilaian_id');
        $existingJawaban = $penilaian ? $penilaian->jawabanPertanyaan->keyBy('pertanyaan_wawancara_id') : collect();

        return view('pewawancara.wawancara-form', compact('jadwal', 'kriteria', 'penilaian', 'existing', 'pertanyaan', 'existingJawaban'));
    }

    public function update(Request $request, JadwalWawancara $jadwal): RedirectResponse
    {
        $this->ensureOwner($request, $jadwal);
        $kriteria = KriteriaPenilaian::where('kegiatan_id', $jadwal->kegiatan_id)->orderBy('urutan')->get();
        abort_if($kriteria->isEmpty(), 422, 'Kriteria penilaian belum tersedia untuk kegiatan ini.');

        $pertanyaan = PertanyaanWawancara::where('kegiatan_id', $jadwal->kegiatan_id)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $rules = [
            'rekomendasi' => ['required', 'in:sangat_direkomendasikan,direkomendasikan,dipertimbangkan,tidak_direkomendasikan'],
            'catatan' => ['nullable', 'string'],
        ];

        $adaPertanyaan = $pertanyaan->isNotEmpty();

        if ($adaPertanyaan) {
            // Mode jawaban per pertanyaan: skor aspek dihitung otomatis.
            foreach ($pertanyaan as $p) {
                if ($p->jenis === 'naratif') {
                    $rules['jawaban.' . $p->id] = ['nullable', 'string', 'max:2000'];
                    $rules['skor_q.' . $p->id] = ['required', 'integer', 'min:0', 'max:100'];
                } else {
                    $rules['pilihan.' . $p->id] = ['required', 'string'];
                }
            }
        }

        // Aspek tanpa pertanyaan tetap memakai input skor manual.
        foreach ($kriteria as $k) {
            if ($pertanyaan->where('kriteria_penilaian_id', $k->id)->isEmpty()) {
                $rules['skor.' . $k->id] = ['required', 'integer', 'min:0', 'max:100'];
            }
        }

        $data = $request->validate($rules);

        $skorAspek = [];      // kriteria_penilaian_id => skor 0-100
        $jawabanRows = [];    // pertanyaan_id => [jawaban, pilihan, skor]
        $nilaiAkhir = 0.0;

        foreach ($kriteria as $k) {
            $qs = $pertanyaan->where('kriteria_penilaian_id', $k->id);

            if ($qs->isEmpty()) {
                $skorAspek[$k->id] = (int) $data['skor'][$k->id];
            } else {
                foreach ($qs as $p) {
                    if ($p->jenis === 'naratif') {
                        $skorP = (int) $data['skor_q'][$p->id];
                        $jawabanRows[$p->id] = [
                            'jawaban' => $data['jawaban'][$p->id] ?? null,
                            'pilihan' => null,
                            'skor' => $skorP,
                        ];
                    } else {
                        $teksPilihan = $data['pilihan'][$p->id];
                        $opsi = collect($p->pilihan ?? [])->firstWhere('teks', $teksPilihan);
                        abort_if($opsi === null, 422, "Pilihan tidak valid untuk pertanyaan #{$p->id}.");
                        $skorP = (int) $opsi['skor'];
                        $jawabanRows[$p->id] = [
                            'jawaban' => null,
                            'pilihan' => $teksPilihan,
                            'skor' => $skorP,
                        ];
                    }
                }

                $totalBobot = max((int) $qs->sum('bobot'), 1);
                $jumlah = $qs->reduce(fn ($carry, $p) => $carry + $jawabanRows[$p->id]['skor'] * $p->bobot, 0);
                $skorAspek[$k->id] = (int) round($jumlah / $totalBobot);
            }

            $nilaiAkhir += $skorAspek[$k->id] * ($k->bobot_persen / 100);
        }

        $nilaiAkhir = round($nilaiAkhir, 2);

        DB::transaction(function () use ($jadwal, $kriteria, $skorAspek, $jawabanRows, $data, $nilaiAkhir) {
            $penilaian = PenilaianWawancara::updateOrCreate(
                ['jadwal_wawancara_id' => $jadwal->id],
                [
                    'pewawancara_id' => $jadwal->pewawancara_id,
                    'nilai_akhir' => $nilaiAkhir,
                    'rekomendasi' => $data['rekomendasi'],
                    'catatan' => $data['catatan'] ?? null,
                    'submitted_at' => now(),
                ]
            );

            foreach ($kriteria as $k) {
                PenilaianDetail::updateOrCreate(
                    [
                        'penilaian_wawancara_id' => $penilaian->id,
                        'kriteria_penilaian_id' => $k->id,
                    ],
                    ['skor' => $skorAspek[$k->id]]
                );
            }

            foreach ($jawabanRows as $pertanyaanId => $row) {
                JawabanPertanyaan::updateOrCreate(
                    [
                        'penilaian_wawancara_id' => $penilaian->id,
                        'pertanyaan_wawancara_id' => $pertanyaanId,
                    ],
                    [
                        'jawaban' => $row['jawaban'],
                        'pilihan' => $row['pilihan'],
                        'skor' => $row['skor'],
                    ]
                );
            }
        });

        $redirect = $request->user()->role === 'admin'
            ? redirect()->route('jadwal')
            : redirect()->route('pewawancara.jadwal.show', $jadwal);

        return $redirect->with('status', 'Penilaian wawancara berhasil disimpan. Nilai akhir: ' . $nilaiAkhir);
    }

    protected function ensureOwner(Request $request, JadwalWawancara $jadwal): void
    {
        abort_unless($jadwal->pewawancara_id === $request->user()->id || $request->user()->role === 'admin', 403);
    }
}
