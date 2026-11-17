<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Imports\HasilSeleksiImport;
use App\Models\Kegiatan;
use App\Models\PesertaSeleksi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class HasilController extends Controller
{
    public function index(Request $request): View
    {
        $kegiatanAktif = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();
        $kegiatanId = $request->integer('kegiatan_id') ?: ($kegiatanAktif->id ?? null);
        $kegiatanList = Kegiatan::orderByDesc('tahun')->orderByDesc('id')->get();
        $kegiatan = $kegiatanId ? Kegiatan::find($kegiatanId) : $kegiatanAktif;

        $q = trim((string) $request->input('q', ''));
        $statusHasil = (string) $request->input('status_hasil', '');
        $statusValid = ['belum_ditentukan', 'lulus', 'tidak_lulus', 'cadangan'];

        $peserta = collect();
        if ($kegiatanId) {
            $query = PesertaSeleksi::with([
                'user.mitraProfile',
                'jadwalWawancara.penilaian',
                'jadwalWawancara.pewawancara',
            ])->where('kegiatan_id', $kegiatanId);

            if ($q !== '') {
                $query->whereHas('user', function ($uq) use ($q) {
                    $uq->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('sobat_id', 'like', "%{$q}%");
                });
            }
            if (in_array($statusHasil, $statusValid, true)) {
                $query->where('status_hasil', $statusHasil);
            }

            $peserta = $query->get()->map(function ($p) {
                $penilaian = $p->jadwalWawancara->map->penilaian->filter()->sortByDesc('submitted_at')->first();
                $p->nilai_akhir_view = $penilaian->nilai_akhir ?? null;
                $p->rekomendasi_view = $penilaian->rekomendasi ?? null;
                $p->pewawancara_view = optional(optional($p->jadwalWawancara->first())->pewawancara)->name;
                return $p;
            })->sortBy(fn($p) => $p->user->name ?? '');
        }

        return view('operator.pengumuman.index', [
            'peserta' => $peserta,
            'kegiatanList' => $kegiatanList,
            'kegiatanId' => $kegiatanId,
            'kegiatan' => $kegiatan,
            'filters' => ['q' => $q, 'status_hasil' => $statusHasil],
        ]);
    }

    public function updateStatus(Request $request, PesertaSeleksi $peserta): RedirectResponse
    {
        $data = $request->validate([
            'status_hasil' => ['required','in:belum_ditentukan,lulus,tidak_lulus,cadangan'],
            'catatan_hasil' => ['nullable','string'],
        ]);
        $peserta->update($data);
        return back()->with('status','Status hasil peserta diperbarui.');
    }

    public function togglePublikasi(Request $request, Kegiatan $kegiatan): RedirectResponse
    {
        $kegiatan->pengumuman_dipublikasikan = ! $kegiatan->pengumuman_dipublikasikan;
        $kegiatan->save();
        return back()->with('status', 'Status publikasi pengumuman: '.($kegiatan->pengumuman_dipublikasikan ? 'DIPUBLIKASIKAN' : 'DISEMBUNYIKAN').'.');
    }

    public function importHasil(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240']]);
        $kegiatan = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();
        if (!$kegiatan) return back()->withErrors(['file'=>'Tidak ada kegiatan aktif.']);
        $import = new HasilSeleksiImport($kegiatan);
        Excel::import($import, $request->file('file'));
        return back()->with('import_hasil_summary', $import->summary);
    }

    public function templateHasil(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new \App\Exports\HasilSeleksiTemplateExport(), 'template_hasil_seleksi.xlsx');
    }

    public function exportRekap(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $kegiatanAktif = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();
        $kegiatanId = $request->integer('kegiatan_id') ?: ($kegiatanAktif->id ?? 0);
        abort_unless($kegiatanId > 0, 404, 'Kegiatan tidak ditemukan.');

        return Excel::download(new \App\Exports\RekapNilaiExport($kegiatanId), "rekap_nilai_kegiatan_{$kegiatanId}.xlsx");
    }
}
