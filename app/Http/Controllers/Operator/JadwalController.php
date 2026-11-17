<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Imports\JadwalWawancaraImport;
use App\Models\JadwalWawancara;
use App\Models\Kegiatan;
use App\Models\PesertaSeleksi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class JadwalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $kegiatanAktif = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();
        $kegiatanId = $request->integer('kegiatan_id') ?: ($kegiatanAktif->id ?? null);
        $kegiatanList = Kegiatan::orderByDesc('tahun')->orderByDesc('id')->get();

        $query = JadwalWawancara::with(['kegiatan', 'pesertaSeleksi.user', 'pewawancara'])
            ->orderBy('tanggal')->orderBy('jam_mulai');
        if ($kegiatanId) $query->where('kegiatan_id', $kegiatanId);
        if ($user->role === 'pewawancara') $query->where('pewawancara_id', $user->id);

        $jadwal = $query->paginate(20)->withQueryString();
        $peserta = $kegiatanId ? PesertaSeleksi::with('user')->where('kegiatan_id', $kegiatanId)->get() : collect();
        $pewawancara = User::where('role', 'pewawancara')->where('is_active', true)->orderBy('name')->get();
        $canManage = in_array($user->role, ['admin', 'operator'], true);

        return view('operator.jadwal.index', compact('jadwal','kegiatanList','kegiatanId','kegiatanAktif','peserta','pewawancara','canManage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManage($request);
        $data = $request->validate([
            'kegiatan_id' => ['required','exists:kegiatan,id'],
            'peserta_seleksi_id' => ['required','exists:peserta_seleksi,id'],
            'pewawancara_id' => ['required','exists:users,id'],
            'tanggal' => ['required','date'],
            'jam_mulai' => ['required'],
            'jam_selesai' => ['required'],
            'sesi' => ['nullable','string','max:100'],
            'metode' => ['required','in:luring,daring'],
            'lokasi_atau_link' => ['required','string','max:255'],
            'catatan_jadwal' => ['nullable','string'],
        ]);
        JadwalWawancara::create($data);
        return back()->with('status','Jadwal wawancara berhasil ditambahkan.');
    }

    public function destroy(Request $request, JadwalWawancara $jadwal): RedirectResponse
    {
        $this->authorizeManage($request);
        $jadwal->delete();
        return back()->with('status','Jadwal dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorizeManage($request);
        $request->validate(['file' => ['required','file','mimes:xlsx,xls,csv']]);
        $kegiatan = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();
        if (!$kegiatan) return back()->withErrors(['file' => 'Tidak ada kegiatan aktif.']);
        $import = new JadwalWawancaraImport($kegiatan);
        Excel::import($import, $request->file('file'));
        return back()->with('import_jadwal_summary', $import->summary);
    }

    public function template(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorizeManage(request());
        return Excel::download(new \App\Exports\JadwalWawancaraTemplateExport(), 'template_jadwal_wawancara.xlsx');
    }

    protected function authorizeManage(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['admin','operator'], true), 403);
    }
}
