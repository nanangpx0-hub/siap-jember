<?php

use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/lupa-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/lupa-password', [ForgotPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/ganti-password', [ChangePasswordController::class, 'edit'])->name('password.change.form');
    Route::post('/ganti-password', [ChangePasswordController::class, 'update'])->name('password.change.update');

    Route::get('/dashboard', function () {
        $user = request()->user();
        return match ($user->role) {
            'superadmin', 'operator' => redirect('/operator/dashboard'),
            'pewawancara' => redirect('/pewawancara/dashboard'),
            'peserta' => redirect('/peserta/dashboard'),
            default => abort(403),
        };
    })->name('dashboard');

    // Dispatcher route kontrak utama (SPEC) berbasis role + menjaga url.intended
    Route::get('/jadwal', function (Request $request) {
        return match ($request->user()->role) {
            'peserta' => app()->call([app(\App\Http\Controllers\Peserta\JadwalController::class), 'index']),
            'superadmin', 'operator', 'pewawancara' => app()->call([app(\App\Http\Controllers\Operator\JadwalController::class), 'index']),
            default => abort(403),
        };
    })->name('jadwal');

    Route::get('/administrasi', function (Request $request) {
        return match ($request->user()->role) {
            'peserta' => app()->call([app(\App\Http\Controllers\Peserta\AdministrasiController::class), 'index']),
            'superadmin', 'operator' => app()->call([app(\App\Http\Controllers\Operator\AdministrasiController::class), 'index']),
            default => abort(403),
        };
    })->name('administrasi');

    Route::get('/pengumuman', function (Request $request) {
        return match ($request->user()->role) {
            'peserta' => app()->call([app(\App\Http\Controllers\Peserta\PengumumanController::class), 'index']),
            'superadmin', 'operator' => app()->call([app(\App\Http\Controllers\Operator\HasilController::class), 'index']),
            default => abort(403),
        };
    })->name('pengumuman');
});

foreach (['peserta', 'operator', 'pewawancara'] as $file) {
    $path = __DIR__.'/'.$file.'.php';
    if (file_exists($path)) {
        require $path;
    }
}
