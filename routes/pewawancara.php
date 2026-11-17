<?php

use App\Http\Controllers\Pewawancara\DashboardController;
use App\Http\Controllers\Pewawancara\JadwalController;
use App\Http\Controllers\Pewawancara\WawancaraController;
use Illuminate\Support\Facades\Route;

// Panel pewawancara: role pewawancara (pemilik jadwal) + superadmin (override).
Route::middleware(['auth', 'role:pewawancara,superadmin'])->group(function () {
    Route::prefix('pewawancara')->name('pewawancara.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/jadwal/{jadwal}', [JadwalController::class, 'show'])->name('jadwal.show');
        Route::get('/jadwal/{jadwal}/cv/preview', [JadwalController::class, 'previewCv'])->name('jadwal.cv.preview');
        Route::get('/jadwal/{jadwal}/cv/download', [JadwalController::class, 'downloadCv'])->name('jadwal.cv.download');
    });
});

// Form penilaian: pewawancara pemilik jadwal (validasi di controller) + superadmin (override).
Route::middleware(['auth', 'role:pewawancara,superadmin'])->group(function () {
    Route::get('/wawancara/{jadwal}', [WawancaraController::class, 'edit'])->name('wawancara.edit');
    Route::post('/wawancara/{jadwal}', [WawancaraController::class, 'update'])->name('wawancara.update');
});
