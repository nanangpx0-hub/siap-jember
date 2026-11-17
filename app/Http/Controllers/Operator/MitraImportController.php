<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Imports\MitraMasterImport;
use App\Imports\PesertaTerpilihImport;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MitraImportController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()
            ->where('role', 'peserta')
            ->with('mitraProfile');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('sobat_id', 'like', "%{$search}%");
            });
        }

        if ($kec = trim((string) $request->input('kecamatan'))) {
            $query->whereHas('mitraProfile', function ($q) use ($kec) {
                $q->where('alamat_kecamatan', 'like', "%{$kec}%");
            });
        }

        if ($capi = $request->input('berpengalaman_capi')) {
            $query->whereHas('mitraProfile', function ($q) use ($capi) {
                $q->where('berpengalaman_capi', 'like', "%{$capi}%");
            });
        }

        $mitra = $query->orderBy('name')->paginate(20)->withQueryString();
        $kegiatanAktif = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();

        $kecamatanOptions = \App\Models\MitraProfile::query()
            ->whereNotNull('alamat_kecamatan')
            ->where('alamat_kecamatan', '!=', '')
            ->distinct()
            ->orderBy('alamat_kecamatan')
            ->pluck('alamat_kecamatan');

        return view('operator.mitra.index', [
            'mitra' => $mitra,
            'kegiatanAktif' => $kegiatanAktif,
            'kecamatanOptions' => $kecamatanOptions,
            'filters' => [
                'q' => $request->input('q'),
                'kecamatan' => $request->input('kecamatan'),
                'berpengalaman_capi' => $request->input('berpengalaman_capi'),
            ],
        ]);
    }

    public function importMaster(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $import = new MitraMasterImport();
        Excel::import($import, $request->file('file'));

        return back()->with('import_master_summary', $import->summary);
    }

    public function importPeserta(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $kegiatan = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();
        if (! $kegiatan) {
            return back()->withErrors(['file' => 'Tidak ada kegiatan aktif.']);
        }

        $import = new PesertaTerpilihImport($kegiatan);
        Excel::import($import, $request->file('file'));

        return back()->with('import_peserta_summary', $import->summary)->with('kegiatan_nama', $kegiatan->nama_kegiatan);
    }

    public function templatePeserta(): BinaryFileResponse
    {
        $export = new \App\Exports\PesertaTerpilihTemplateExport();
        return Excel::download($export, 'template_peserta_terpilih.xlsx');
    }
}
