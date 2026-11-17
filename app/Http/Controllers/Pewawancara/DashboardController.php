<?php

namespace App\Http\Controllers\Pewawancara;

use App\Http\Controllers\Controller;
use App\Models\JadwalWawancara;
use App\Models\TugasPanitia;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tugas = TugasPanitia::with('kegiatan')
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        $jadwal = JadwalWawancara::with(['kegiatan', 'pesertaSeleksi.user', 'penilaian'])
            ->where('pewawancara_id', $user->id)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        return view('dashboards.pewawancara', compact('tugas', 'jadwal'));
    }
}
