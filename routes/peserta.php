<?php

use App\Http\Controllers\Peserta\AdministrasiController;
use App\Http\Controllers\Peserta\DashboardController;
use App\Http\Controllers\Peserta\JadwalController;
use App\Http\Controllers\Peserta\PengumumanController;
use App\Http\Controllers\Peserta\UndanganController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:peserta'])->prefix('peserta')->name('peserta.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.detail');
});

Route::middleware(['auth', 'role:peserta'])->group(function () {
    Route::get('/undangan', [UndanganController::class, 'index'])->name('peserta.undangan');
    Route::post('/undangan/rsvp', [UndanganController::class, 'rsvp'])->name('peserta.undangan.rsvp');

    // Aksi administrasi peserta (CV)
    Route::post('/administrasi/upload', [AdministrasiController::class, 'upload'])->name('peserta.administrasi.upload');
    Route::get('/administrasi/cv/download', [AdministrasiController::class, 'download'])->name('peserta.administrasi.download');
    Route::get('/administrasi/cv/preview', [AdministrasiController::class, 'preview'])->name('peserta.administrasi.preview');
});

// Catatan: route kontrak GET /jadwal, /administrasi, /pengumuman didispatch berbasis role di routes/web.php
// agar sesuai SPEC dan tetap menjaga url.intended. Controller peserta tetap digunakan untuk role peserta.
