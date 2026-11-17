<?php

namespace App\Http\Controllers\Operator;

use App\Exports\PewawancaraTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\PewawancaraImport;
use App\Models\Kegiatan;
use App\Models\TugasPanitia;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TugasController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $kegiatan = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();
        $kegiatanId = $request->integer('kegiatan_id') ?: ($kegiatan->id ?? null);

        $query = TugasPanitia::with(['user', 'kegiatan'])->orderByDesc('id');
        if ($kegiatanId) {
            $query->where('kegiatan_id', $kegiatanId);
        }
        if ($user->role === 'pewawancara') {
            $query->where('user_id', $user->id);
        }

        $tugas = $query->paginate(20)->withQueryString();
        $kegiatanList = Kegiatan::orderByDesc('tahun')->orderByDesc('id')->get();
        $pewawancara = User::whereIn('role', ['pewawancara', 'operator', 'superadmin'])->where('is_active', true)->orderBy('name')->get();

        return view('operator.tugas.index', [
            'tugas' => $tugas,
            'kegiatanList' => $kegiatanList,
            'kegiatanId' => $kegiatanId,
            'kegiatanAktif' => $kegiatan,
            'pewawancara' => $pewawancara,
            'canManage' => in_array($user->role, ['superadmin', 'operator'], true),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManage($request);
        $data = $request->validate([
            'kegiatan_id' => ['required', 'exists:kegiatan,id'],
            'user_id' => ['required', 'exists:users,id'],
            'peran_tugas' => ['required', 'string', 'max:255'],
            'wilayah_atau_ruang' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ]);
        TugasPanitia::create($data);
        return back()->with('status', 'Tugas panitia berhasil ditambahkan.');
    }

    public function destroy(Request $request, TugasPanitia $tugas): RedirectResponse
    {
        $this->authorizeManage($request);
        $tugas->delete();
        return back()->with('status', 'Tugas panitia dihapus.');
    }

    public function storePewawancara(Request $request): RedirectResponse
    {
        $this->authorizeManage($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'no_telp' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8'],
            'must_change_password' => ['nullable', 'boolean'],
        ]);
        User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'no_telp' => $data['no_telp'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'pewawancara',
            'must_change_password' => $request->boolean('must_change_password', true),
            'is_active' => true,
        ]);
        return back()->with('status', 'Akun pewawancara berhasil dibuat.');
    }

    public function storeKegiatan(Request $request): RedirectResponse
    {
        $this->authorizeManage($request);
        $data = $request->validate([
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'tahun' => ['required', 'integer', 'digits:4'],
            'deskripsi' => ['nullable', 'string'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $kegiatan = DB::transaction(function () use ($data) {
            $isActive = (bool) ($data['is_active'] ?? false);
            if ($isActive) {
                Kegiatan::where('is_active', true)->update(['is_active' => false]);
            }

            $kegiatan = Kegiatan::create([
                'nama_kegiatan' => $data['nama_kegiatan'],
                'tahun' => $data['tahun'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'tanggal_mulai' => $data['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
                'is_active' => $isActive,
                'pengumuman_dipublikasikan' => false,
            ]);

            $kegiatan->seedDefaultKriteriaDanPertanyaan();

            return $kegiatan;
        });

        return back()->with('status', "Kegiatan '{$kegiatan->nama_kegiatan}' berhasil ditambahkan beserta 4 kriteria dan 17 pertanyaan default.");
    }

    public function destroyKegiatan(Request $request, Kegiatan $kegiatan): RedirectResponse
    {
        $this->authorizeManage($request);

        $terkait = array_keys(array_filter([
            'tugas panitia' => $kegiatan->tugasPanitia()->exists(),
            'peserta seleksi' => $kegiatan->pesertaSeleksi()->exists(),
            'jadwal wawancara' => $kegiatan->jadwalWawancara()->exists(),
            'dokumen penyelenggaraan' => $kegiatan->dokumenPenyelenggaraan()->exists(),
        ]));

        if ($terkait) {
            return back()->withErrors([
                'kegiatan' => 'Kegiatan tidak bisa dihapus karena masih memiliki data: '.implode(', ', $terkait).'. Hapus data terkait terlebih dahulu.',
            ]);
        }

        $kegiatan->delete();

        return back()->with('status', 'Kegiatan berhasil dihapus.');
    }

    public function activateKegiatan(Request $request, Kegiatan $kegiatan): RedirectResponse
    {
        $this->authorizeManage($request);
        DB::transaction(function () use ($kegiatan) {
            Kegiatan::where('id', '!=', $kegiatan->id)->update(['is_active' => false]);
            $kegiatan->update(['is_active' => true]);
        });

        return back()->with('status', "Kegiatan '{$kegiatan->nama_kegiatan}' diaktifkan.");
    }

    public function importPewawancara(Request $request): RedirectResponse
    {
        $this->authorizeManage($request);
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $import = new PewawancaraImport();
        Excel::import($import, $request->file('file'));

        return back()->with('import_pewawancara_summary', $import->summary);
    }

    public function templatePewawancara(): BinaryFileResponse
    {
        $this->authorizeManage(request());

        return Excel::download(new PewawancaraTemplateExport(), 'template_akun_pewawancara.xlsx');
    }

    protected function authorizeManage(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['superadmin', 'operator'], true), 403);
    }
}
