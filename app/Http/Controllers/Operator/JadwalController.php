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
use Illuminate\Validation\Rule;
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

        $query = JadwalWawancara::with(['kegiatan', 'pesertaSeleksi.user', 'pewawancara', 'penilaian'])
            ->orderBy('tanggal')->orderBy('jam_mulai');
        if ($kegiatanId) $query->where('kegiatan_id', $kegiatanId);
        if ($user->role === 'pewawancara') $query->where('pewawancara_id', $user->id);

        $jadwal = $query->paginate(20)->withQueryString();
        $peserta = $kegiatanId ? PesertaSeleksi::with('user')->where('kegiatan_id', $kegiatanId)->get() : collect();
        $pewawancara = User::whereIn('role', ['pewawancara', 'superadmin'])->where('is_active', true)->orderBy('name')->get();
        $canManage = in_array($user->role, ['superadmin', 'operator'], true);

        return view('operator.jadwal.index', compact('jadwal','kegiatanList','kegiatanId','kegiatanAktif','peserta','pewawancara','canManage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManage($request);
        $data = $request->validate([
            'kegiatan_id' => ['required', 'exists:kegiatan,id'],
            'peserta_seleksi_id' => [
                'required',
                Rule::exists('peserta_seleksi', 'id')->where('kegiatan_id', $request->input('kegiatan_id')),
            ],
            'pewawancara_id' => [
                'required',
                Rule::exists('users', 'id')->where('is_active', true)->whereIn('role', ['pewawancara', 'superadmin']),
            ],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'sesi' => ['nullable', 'string', 'max:100'],
            'metode' => ['required', 'in:luring,daring'],
            'lokasi_atau_link' => ['required', 'string', 'max:255'],
            'catatan_jadwal' => ['nullable', 'string'],
        ]);
        JadwalWawancara::updateOrCreate(
            [
                'kegiatan_id' => $data['kegiatan_id'],
                'peserta_seleksi_id' => $data['peserta_seleksi_id'],
            ],
            $data
        );
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
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240']]);
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
        abort_unless(in_array($request->user()->role, ['superadmin','operator'], true), 403);
    }
}
