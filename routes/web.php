<?php

use App\Http\Controllers\Admin\AturTanggalController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\KonfirmasiPembayaranController;
use App\Http\Controllers\Admin\KonfirmasiPersyaratanController;
use App\Http\Controllers\Admin\ProfilController as AdminProfilController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\SubmissionController as AdminSubmissionController;
use App\Http\Controllers\Admin\TimTerdaftarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Peserta\DokumenController as PesertaDokumenController;
use App\Http\Controllers\Peserta\PembayaranController as PesertaPembayaranController;
use App\Http\Controllers\Peserta\ProfilController as PesertaProfilController;
use App\Http\Controllers\Peserta\SubmissionController as PesertaSubmissionController;
use App\Http\Controllers\Peserta\TimController as PesertaTimController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/debug-request', function () {
    return response()->json([
        'url' => request()->url(),
        'fullUrl' => request()->fullUrl(),
        'scheme' => request()->getScheme(),
        'secure' => request()->secure(),
        'host' => request()->getHost(),
        'port' => request()->getPort(),
        'app_url_config' => config('app.url'),
        'headers' => request()->headers->all(),
        'server' => array_intersect_key($_SERVER, array_flip([
            'HTTP_HOST', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED_PROTO', 'HTTP_X_FORWARDED_PORT',
            'SERVER_PORT', 'REQUEST_SCHEME', 'HTTPS', 'SERVER_NAME', 'SERVER_ADDR',
        ])),
        'app_key' => substr(config('app.key') ?: '', 0, 15).'...',
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Peserta Routes
Route::middleware(['auth', 'verified', 'peserta'])->group(function () {
    Route::get('/dashboard/tim', [PesertaTimController::class, 'index'])->name('peserta.tim');
    Route::post('/dashboard/tim', [PesertaTimController::class, 'store'])->name('peserta.tim.store');

    Route::post('/dashboard/tim/dokumen', [PesertaDokumenController::class, 'store'])->name('peserta.tim.dokumen.store');
    Route::delete('/dashboard/tim/dokumen', [PesertaDokumenController::class, 'destroy'])->name('peserta.tim.dokumen.destroy');

    Route::post('/dashboard/tim/pembayaran', [PesertaPembayaranController::class, 'store'])->name('peserta.tim.pembayaran.store');
    Route::delete('/dashboard/tim/pembayaran', [PesertaPembayaranController::class, 'destroy'])->name('peserta.tim.pembayaran.destroy');

    Route::post('/dashboard/tim/submission', [PesertaSubmissionController::class, 'store'])->name('peserta.tim.submission.store');

    Route::get('/dashboard/profil', [PesertaProfilController::class, 'index'])->name('peserta.profil');
});

// Admin Routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/konfirmasi-persyaratan', [KonfirmasiPersyaratanController::class, 'index'])->name('admin.persyaratan');
    Route::post('/konfirmasi-persyaratan/{id_registrasi}/status', [KonfirmasiPersyaratanController::class, 'update'])->name('admin.persyaratan.update');

    Route::get('/konfirmasi-pembayaran', [KonfirmasiPembayaranController::class, 'index'])->name('admin.pembayaran');
    Route::post('/konfirmasi-pembayaran/{id_pembayaran}/status', [KonfirmasiPembayaranController::class, 'update'])->name('admin.pembayaran.update');

    Route::get('/tim-terdaftar', [TimTerdaftarController::class, 'index'])->name('admin.tim-terdaftar');
    Route::get('/tim-terdaftar/export', [TimTerdaftarController::class, 'export'])->name('admin.tim-terdaftar.export');
    Route::get('/submission', [AdminSubmissionController::class, 'index'])->name('admin.submission');
    Route::get('/submission/export', [AdminSubmissionController::class, 'export'])->name('admin.submission.export');

    Route::get('/atur-tanggal', [AturTanggalController::class, 'index'])->name('admin.atur-tanggal');
    Route::post('/atur-tanggal', [AturTanggalController::class, 'update'])->name('admin.atur-tanggal.update');

    Route::get('/kelola-sponsor', [SponsorController::class, 'index'])->name('admin.kelola-sponsor');
    Route::post('/kelola-sponsor', [SponsorController::class, 'store'])->name('admin.kelola-sponsor.store');
    Route::post('/kelola-sponsor/{id}', [SponsorController::class, 'update'])->name('admin.kelola-sponsor.update');
    Route::delete('/kelola-sponsor/{id}', [SponsorController::class, 'destroy'])->name('admin.kelola-sponsor.destroy');

    Route::get('/profil', [AdminProfilController::class, 'index'])->name('admin.profil');
});

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
