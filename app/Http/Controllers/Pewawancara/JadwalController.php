<?php

namespace App\Http\Controllers\Pewawancara;

use App\Http\Controllers\Controller;
use App\Models\JadwalWawancara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JadwalController extends Controller
{
    public function show(Request $request, JadwalWawancara $jadwal): View
    {
        $this->ensureOwner($request, $jadwal);
        $jadwal->load([
            'kegiatan',
            'pesertaSeleksi.user.mitraProfile',
            'penilaian.details',
        ]);

        $peserta = $jadwal->pesertaSeleksi;
        $profile = optional($peserta->user)->mitraProfile;

        return view('pewawancara.jadwal-show', compact('jadwal', 'peserta', 'profile'));
    }

    public function previewCv(Request $request, JadwalWawancara $jadwal): StreamedResponse
    {
        $this->ensureOwner($request, $jadwal);
        $peserta = $jadwal->pesertaSeleksi;
        abort_unless($peserta && $peserta->cv_path, 404);
        abort_unless(Storage::disk('local')->exists($peserta->cv_path), 404);
        return Storage::disk('local')->response($peserta->cv_path, 'cv.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="cv.pdf"',
        ]);
    }

    public function downloadCv(Request $request, JadwalWawancara $jadwal): StreamedResponse
    {
        $this->ensureOwner($request, $jadwal);
        $peserta = $jadwal->pesertaSeleksi;
        abort_unless($peserta && $peserta->cv_path, 404);
        abort_unless(Storage::disk('local')->exists($peserta->cv_path), 404);
        $name = 'CV_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $peserta->user->name ?? 'peserta') . '.pdf';
        return Storage::disk('local')->download($peserta->cv_path, $name);
    }

    protected function ensureOwner(Request $request, JadwalWawancara $jadwal): void
    {
        abort_unless($jadwal->pewawancara_id === $request->user()->id, 403);
    }
}
