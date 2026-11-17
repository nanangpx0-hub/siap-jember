<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\DokumenPenyelenggaraan;
use App\Models\Kegiatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdministrasiController extends Controller
{
    public function index(Request $request): View
    {
        $kegiatanAktif = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();
        $kegiatanId = $request->integer('kegiatan_id') ?: ($kegiatanAktif->id ?? null);
        $kegiatanList = Kegiatan::orderByDesc('tahun')->orderByDesc('id')->get();

        $query = DokumenPenyelenggaraan::with(['kegiatan','uploader'])->orderByDesc('id');
        if ($kegiatanId) $query->where('kegiatan_id', $kegiatanId);
        $dokumen = $query->paginate(20)->withQueryString();

        $kategori = ['daftar_hadir','dokumentasi','laporan_penyelenggaraan','sk_atau_surat','lainnya'];
        return view('operator.administrasi.index', compact('dokumen','kegiatanList','kegiatanId','kegiatanAktif','kategori'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kegiatan_id' => ['required','exists:kegiatan,id'],
            'kategori' => ['required','in:daftar_hadir,dokumentasi,laporan_penyelenggaraan,sk_atau_surat,lainnya'],
            'judul' => ['required','string','max:255'],
            'file' => ['required','file','max:20480'],
        ]);
        $file = $request->file('file');
        $path = $file->store('private/penyelenggaraan/'.$data['kegiatan_id'], 'local');

        DokumenPenyelenggaraan::create([
            'kegiatan_id' => $data['kegiatan_id'],
            'kategori' => $data['kategori'],
            'judul' => $data['judul'],
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
            'file_size' => $file->getSize() ?: 0,
            'uploaded_by' => $request->user()->id,
        ]);

        return back()->with('status','Berkas penyelenggaraan berhasil diunggah.');
    }

    public function destroy(Request $request, DokumenPenyelenggaraan $dokumen): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['admin','operator'], true), 403);
        if ($dokumen->file_path && Storage::disk('local')->exists($dokumen->file_path)) {
            Storage::disk('local')->delete($dokumen->file_path);
        }
        $dokumen->delete();
        return back()->with('status','Dokumen dihapus.');
    }

    public function download(Request $request, DokumenPenyelenggaraan $dokumen): StreamedResponse
    {
        abort_unless(in_array($request->user()->role, ['admin','operator'], true), 403);
        abort_unless($dokumen->file_path && Storage::disk('local')->exists($dokumen->file_path), 404);
        return Storage::disk('local')->download($dokumen->file_path, $dokumen->original_filename);
    }
}
