<?php

use App\Http\Controllers\Operator\AdministrasiController;
use App\Http\Controllers\Operator\HasilController;
use App\Http\Controllers\Operator\JadwalController;
use App\Http\Controllers\Operator\MitraImportController;
use App\Http\Controllers\Operator\TugasController;
use App\Models\JadwalWawancara;
use App\Models\Kegiatan;
use App\Models\PesertaSeleksi;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:superadmin,operator'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/dashboard', function () {
        $kegiatanAktif = Kegiatan::where('is_active', true)->orderByDesc('tahun')->first();
        $kegiatanId = $kegiatanAktif->id ?? null;

        $totalMitra = User::where('role', 'peserta')->count();
        $totalPeserta = $kegiatanId ? PesertaSeleksi::where('kegiatan_id', $kegiatanId)->count() : 0;
        $totalRsvpHadir = $kegiatanId ? PesertaSeleksi::where('kegiatan_id', $kegiatanId)->where('status_undangan', 'hadir')->count() : 0;
        $totalUploadCv = $kegiatanId ? PesertaSeleksi::where('kegiatan_id', $kegiatanId)->whereNotNull('cv_path')->count() : 0;
        $totalJadwal = $kegiatanId ? JadwalWawancara::where('kegiatan_id', $kegiatanId)->count() : 0;
        $totalDinilai = $kegiatanId ? JadwalWawancara::where('kegiatan_id', $kegiatanId)->whereHas('penilaian')->count() : 0;

        return view('dashboards.operator', compact(
            'totalMitra', 'kegiatanAktif', 'totalPeserta',
            'totalRsvpHadir', 'totalUploadCv', 'totalJadwal', 'totalDinilai'
        ));
    })->name('dashboard');

    // Mitra (Track 2)
    Route::get('/mitra', [MitraImportController::class, 'index'])->name('mitra.index');
    Route::post('/mitra/import-master', [MitraImportController::class, 'importMaster'])->name('mitra.import-master');
    Route::post('/mitra/import-peserta', [MitraImportController::class, 'importPeserta'])->name('mitra.import-peserta');
    Route::get('/mitra/template-peserta', [MitraImportController::class, 'templatePeserta'])->name('mitra.template-peserta');

    // Administrasi/Penyelenggaraan (T4.3) - panel operator
    Route::get('/administrasi', [AdministrasiController::class, 'index'])->name('administrasi');
    Route::post('/administrasi', [AdministrasiController::class, 'store'])->name('administrasi.store');
    Route::get('/administrasi/{dokumen}/download', [AdministrasiController::class, 'download'])->name('administrasi.download');
    Route::delete('/administrasi/{dokumen}', [AdministrasiController::class, 'destroy'])->name('administrasi.destroy');

    // Pengumuman/Hasil (T4.4) - panel operator
    Route::get('/pengumuman', [HasilController::class, 'index'])->name('pengumuman');
    Route::post('/pengumuman/kegiatan/{kegiatan}/toggle', [HasilController::class, 'togglePublikasi'])->name('pengumuman.toggle');
    Route::post('/pengumuman/peserta/{peserta}/status', [HasilController::class, 'updateStatus'])->name('pengumuman.status');
    Route::post('/pengumuman/import-hasil', [HasilController::class, 'importHasil'])->name('pengumuman.import-hasil');
    Route::get('/pengumuman/template-hasil', [HasilController::class, 'templateHasil'])->name('pengumuman.template-hasil');
    Route::get('/pengumuman/export-rekap', [HasilController::class, 'exportRekap'])->name('pengumuman.export-rekap');
});

// Akses bersama superadmin, operator & pewawancara untuk /tugas; aksi kelola dibatasi di controller.
Route::middleware(['auth', 'role:superadmin,operator,pewawancara'])->group(function () {
    Route::get('/tugas', [TugasController::class, 'index'])->name('tugas');
    // GET /jadwal kontrak ditangani dispatcher berbasis role di routes/web.php
});

Route::middleware(['auth', 'role:superadmin,operator'])->group(function () {
    // Tugas manage
    Route::post('/tugas', [TugasController::class, 'store'])->name('tugas.store');
    Route::delete('/tugas/{tugas}', [TugasController::class, 'destroy'])->name('tugas.destroy');
    Route::post('/tugas/kegiatan', [TugasController::class, 'storeKegiatan'])->name('tugas.kegiatan.store');
    Route::delete('/tugas/kegiatan/{kegiatan}', [TugasController::class, 'destroyKegiatan'])->name('tugas.kegiatan.destroy');
    Route::post('/tugas/kegiatan/{kegiatan}/activate', [TugasController::class, 'activateKegiatan'])->name('tugas.kegiatan.activate');
    Route::post('/tugas/pewawancara', [TugasController::class, 'storePewawancara'])->name('tugas.pewawancara.store');
    Route::post('/tugas/pewawancara/import', [TugasController::class, 'importPewawancara'])->name('tugas.pewawancara.import');
    Route::get('/tugas/pewawancara/template', [TugasController::class, 'templatePewawancara'])->name('tugas.pewawancara.template');

    // Jadwal manage (superadmin/operator)
    Route::post('/jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
    Route::delete('/jadwal/{jadwal}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');
    Route::post('/jadwal/import', [JadwalController::class, 'import'])->name('jadwal.import');
    Route::get('/jadwal/template', [JadwalController::class, 'template'])->name('jadwal.template');
});
