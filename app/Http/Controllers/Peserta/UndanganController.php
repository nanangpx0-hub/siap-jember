<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UndanganController extends Controller
{
    public function index(Request $request): View
    {
        [$kegiatan, $pesertaSeleksi] = $this->getActive($request);
        return view('peserta.undangan', compact('kegiatan', 'pesertaSeleksi'));
    }

    public function rsvp(Request $request): RedirectResponse
    {
        [$kegiatan, $pesertaSeleksi] = $this->getActive($request);
        abort_unless($pesertaSeleksi, 403, 'Anda tidak terdaftar sebagai peserta seleksi pada kegiatan aktif.');

        $data = $request->validate([
            'status_undangan' => ['required', 'in:hadir,tidak_hadir'],
        ]);

        $pesertaSeleksi->update([
            'status_undangan' => $data['status_undangan'],
            'waktu_konfirmasi' => now(),
        ]);

        return back()->with('status', 'Konfirmasi kehadiran (RSVP) berhasil disimpan.');
    }

    protected function getActive(Request $request): array
    {
        $kegiatan = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();
        $pesertaSeleksi = $kegiatan
            ? $request->user()->pesertaSeleksi()->with(['jadwalWawancara.pewawancara'])->where('kegiatan_id', $kegiatan->id)->first()
            : null;
        return [$kegiatan, $pesertaSeleksi];
    }
}
