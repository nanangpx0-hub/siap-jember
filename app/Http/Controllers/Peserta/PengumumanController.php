<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengumumanController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $kegiatan = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();
        $pesertaSeleksi = $kegiatan
            ? $user->pesertaSeleksi()->where('kegiatan_id', $kegiatan->id)->first()
            : null;
        $dipublikasikan = $kegiatan && $kegiatan->pengumuman_dipublikasikan;

        return view('peserta.pengumuman', compact('kegiatan', 'pesertaSeleksi', 'dipublikasikan'));
    }
}
