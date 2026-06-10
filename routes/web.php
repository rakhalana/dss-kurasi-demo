<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return view('login');
})->name('login');

Route::post('/', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Forgot Password Routes
Route::get('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [\App\Http\Controllers\PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [\App\Http\Controllers\PasswordResetController::class, 'reset'])->name('password.update');
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // Kriteria Management
    Route::get('/admin/kriteria', [\App\Http\Controllers\KriteriaController::class, 'index'])->name('admin.kriteria');
    Route::post('/admin/kriteria/{id}', [\App\Http\Controllers\KriteriaController::class, 'update'])->name('admin.kriteria.update');
    Route::post('/admin/kriteria/skala/toggle', [\App\Http\Controllers\KriteriaController::class, 'toggleSkala'])->name('admin.kriteria.toggle-skala');
    Route::post('/admin/kriteria/skala/update', [\App\Http\Controllers\KriteriaController::class, 'updateSkala'])->name('admin.kriteria.update-skala');
    
    // Bobot Kriteria Management
    Route::get('/admin/bobot', [\App\Http\Controllers\BobotKriteriaController::class, 'index'])->name('admin.bobot.index');
    Route::post('/admin/bobot/calculate', [\App\Http\Controllers\BobotKriteriaController::class, 'calculate'])->name('admin.bobot.calculate');
    
    // User Management
    Route::get('/admin/user', [\App\Http\Controllers\UserController::class, 'index'])->name('admin.user');
    Route::post('/admin/user', [\App\Http\Controllers\UserController::class, 'store'])->name('admin.user.store');
    Route::post('/admin/user/{id}/update', [\App\Http\Controllers\UserController::class, 'update'])->name('admin.user.update');
    Route::post('/admin/user/{id}/delete', [\App\Http\Controllers\UserController::class, 'destroy'])->name('admin.user.delete');

    // Produk Management
    Route::get('/admin/produk', [\App\Http\Controllers\ProdukController::class, 'index'])->name('admin.produk');
    Route::post('/admin/produk', [\App\Http\Controllers\ProdukController::class, 'store'])->name('admin.produk.store');
    Route::post('/admin/produk/{id}/update', [\App\Http\Controllers\ProdukController::class, 'update'])->name('admin.produk.update');
    Route::post('/admin/produk/{id}/delete', [\App\Http\Controllers\ProdukController::class, 'destroy'])->name('admin.produk.delete');
    Route::post('/admin/produk/{id}/legalitas', [\App\Http\Controllers\ProdukController::class, 'updateLegalitas'])->name('admin.produk.legalitas');
    Route::get('/admin/produk/template', [\App\Http\Controllers\ProdukController::class, 'downloadTemplate'])->name('admin.produk.template');
    Route::post('/admin/produk/import', [\App\Http\Controllers\ProdukController::class, 'import'])->name('admin.produk.import');

    // Manajemen Kurasi (Periode)
    Route::get('/admin/kurasi', [\App\Http\Controllers\PeriodeKurasiController::class, 'index'])->name('admin.kurasi.index');
    Route::post('/admin/kurasi', [\App\Http\Controllers\PeriodeKurasiController::class, 'store'])->name('admin.kurasi.store');
    Route::post('/admin/kurasi/{id}/update', [\App\Http\Controllers\PeriodeKurasiController::class, 'update'])->name('admin.kurasi.update');
    Route::post('/admin/kurasi/{id}/delete', [\App\Http\Controllers\PeriodeKurasiController::class, 'destroy'])->name('admin.kurasi.delete');

    // Manajemen Produk dalam Periode Kurasi
    Route::get('/admin/kurasi/{id}/produk', [\App\Http\Controllers\PeriodeKurasiController::class, 'manageProduk'])->name('admin.kurasi.produk');
    Route::post('/admin/kurasi/{id}/produk', [\App\Http\Controllers\PeriodeKurasiController::class, 'storeProduk'])->name('admin.kurasi.produk.store');

    // ==========================================
    // ROLE: KURATOR
    // ==========================================
    Route::get('/kurator/penilaian', [\App\Http\Controllers\PenilaianKuratorController::class, 'index'])->name('kurator.penilaian.index');
    Route::get('/kurator/penilaian/{id_periode}', [\App\Http\Controllers\PenilaianKuratorController::class, 'detailPeriode'])->name('kurator.penilaian.detail');
    Route::get('/kurator/penilaian/{id_periode}/workspace/{id_alternatif?}', [\App\Http\Controllers\PenilaianKuratorController::class, 'workspace'])->name('kurator.penilaian.workspace');
    Route::post('/kurator/penilaian/{id_periode}/workspace/{id_alternatif}/kriteria/{id_kriteria}', [\App\Http\Controllers\PenilaianKuratorController::class, 'storePenilaian'])->name('kurator.penilaian.store');
    Route::post('/kurator/penilaian/{id_periode}/workspace/{id_alternatif}/komentar', [\App\Http\Controllers\PenilaianKuratorController::class, 'storeKomentar'])->name('kurator.penilaian.komentar');
    Route::post('/kurator/penilaian/{id_periode}/selesaikan', [\App\Http\Controllers\PenilaianKuratorController::class, 'selesaikanKurasi'])->name('kurator.penilaian.selesaikan');
    Route::get('/kurator/penilaian/{id_periode}/selesai', [\App\Http\Controllers\PenilaianKuratorController::class, 'halamanSelesai'])->name('kurator.penilaian.selesai');

    // ==========================================
    // HASIL KURASI (LEADERBOARD)
    // ==========================================
    Route::get('/hasil-kurasi', [\App\Http\Controllers\HasilKurasiController::class, 'index'])->name('hasil.index');
    Route::get('/hasil-kurasi/{id}', [\App\Http\Controllers\HasilKurasiController::class, 'detail'])->name('hasil.detail');
    Route::get('/hasil-kurasi/{id}/cetak', [\App\Http\Controllers\HasilKurasiController::class, 'cetak'])->name('hasil.cetak');
});