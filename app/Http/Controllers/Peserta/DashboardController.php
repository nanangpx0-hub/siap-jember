<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $kegiatan = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();
        $pesertaSeleksi = $kegiatan
            ? $user->pesertaSeleksi()->with(['jadwalWawancara.pewawancara', 'jadwalWawancara.penilaian'])->where('kegiatan_id', $kegiatan->id)->first()
            : null;

        return view('dashboards.peserta', compact('kegiatan', 'pesertaSeleksi'));
    }
}
