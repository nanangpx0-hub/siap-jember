<?php

use App\Http\Controllers\Operator\AdministrasiController;
use App\Http\Controllers\Operator\HasilController;
use App\Http\Controllers\Operator\JadwalController;
use App\Http\Controllers\Operator\MitraImportController;
use App\Http\Controllers\Operator\TugasController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,operator'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/dashboard', fn () => view('dashboards.operator'))->name('dashboard');

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
});

// Akses bersama admin,operator,pewawancara (T4.1 view; aksi kelola dibatasi di controller)
Route::middleware(['auth', 'role:admin,operator,pewawancara'])->group(function () {
    Route::get('/tugas', [TugasController::class, 'index'])->name('tugas');
    // GET /jadwal kontrak ditangani dispatcher berbasis role di routes/web.php
});

Route::middleware(['auth', 'role:admin,operator'])->group(function () {
    // Tugas manage
    Route::post('/tugas', [TugasController::class, 'store'])->name('tugas.store');
    Route::delete('/tugas/{tugas}', [TugasController::class, 'destroy'])->name('tugas.destroy');
    Route::post('/tugas/pewawancara', [TugasController::class, 'storePewawancara'])->name('tugas.pewawancara.store');

    // Jadwal manage (admin/operator)
    Route::post('/jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
    Route::delete('/jadwal/{jadwal}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');
    Route::post('/jadwal/import', [JadwalController::class, 'import'])->name('jadwal.import');
    Route::get('/jadwal/template', [JadwalController::class, 'template'])->name('jadwal.template');
});
