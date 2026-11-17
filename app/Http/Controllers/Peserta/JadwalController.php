<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $kegiatan = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();
        $pesertaSeleksi = $kegiatan
            ? $user->pesertaSeleksi()->where('kegiatan_id', $kegiatan->id)->first()
            : null;
        $jadwal = $pesertaSeleksi
            ? $pesertaSeleksi->jadwalWawancara()->with('pewawancara')->orderBy('tanggal')->orderBy('jam_mulai')->get()
            : collect();

        return view('peserta.jadwal', compact('kegiatan', 'pesertaSeleksi', 'jadwal'));
    }
}
