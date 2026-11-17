<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\TugasPanitia;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
        $pewawancara = User::whereIn('role', ['pewawancara', 'operator', 'admin'])->where('is_active', true)->orderBy('name')->get();

        return view('operator.tugas.index', [
            'tugas' => $tugas,
            'kegiatanList' => $kegiatanList,
            'kegiatanId' => $kegiatanId,
            'kegiatanAktif' => $kegiatan,
            'pewawancara' => $pewawancara,
            'canManage' => in_array($user->role, ['admin', 'operator'], true),
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

    protected function authorizeManage(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['admin', 'operator'], true), 403);
    }
}
