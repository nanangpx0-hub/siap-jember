<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
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
        [$kegiatan, $pesertaSeleksi] = $this->getActive($request);
        return view('peserta.administrasi', compact('kegiatan', 'pesertaSeleksi'));
    }

    public function upload(Request $request): RedirectResponse
    {
        [$kegiatan, $pesertaSeleksi] = $this->getActive($request);
        abort_unless($pesertaSeleksi, 403, 'Anda tidak terdaftar sebagai peserta seleksi pada kegiatan aktif.');

        $request->validate([
            'cv' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:5120'],
        ]);

        $file = $request->file('cv');
        $dir = 'private/cv/' . $kegiatan->id . '/' . $request->user()->id;
        $path = $file->storeAs($dir, 'cv.pdf', 'local');

        // hapus file lama jika berbeda
        if ($pesertaSeleksi->cv_path && $pesertaSeleksi->cv_path !== $path && Storage::disk('local')->exists($pesertaSeleksi->cv_path)) {
            Storage::disk('local')->delete($pesertaSeleksi->cv_path);
        }

        $pesertaSeleksi->update([
            'cv_path' => $path,
            'cv_uploaded_at' => now(),
        ]);

        return back()->with('status', 'CV (PDF) berhasil diunggah.');
    }

    public function download(Request $request): StreamedResponse
    {
        [$kegiatan, $pesertaSeleksi] = $this->getActive($request);
        abort_unless($pesertaSeleksi && $pesertaSeleksi->cv_path, 404);
        // pastikan milik sendiri
        abort_unless($pesertaSeleksi->user_id === $request->user()->id, 403);
        abort_unless(Storage::disk('local')->exists($pesertaSeleksi->cv_path), 404);
        $name = 'CV_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $request->user()->name) . '.pdf';
        return Storage::disk('local')->download($pesertaSeleksi->cv_path, $name);
    }

    public function preview(Request $request): StreamedResponse
    {
        [$kegiatan, $pesertaSeleksi] = $this->getActive($request);
        abort_unless($pesertaSeleksi && $pesertaSeleksi->cv_path, 404);
        abort_unless($pesertaSeleksi->user_id === $request->user()->id, 403);
        abort_unless(Storage::disk('local')->exists($pesertaSeleksi->cv_path), 404);
        return Storage::disk('local')->response($pesertaSeleksi->cv_path, 'cv.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="cv.pdf"',
        ]);
    }

    protected function getActive(Request $request): array
    {
        $kegiatan = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();
        $pesertaSeleksi = $kegiatan
            ? $request->user()->pesertaSeleksi()->where('kegiatan_id', $kegiatan->id)->first()
            : null;
        return [$kegiatan, $pesertaSeleksi];
    }
}
