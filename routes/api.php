<?php

use App\Http\Controllers\Api\AlatController as ApiAlatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BagianController;
use App\Http\Controllers\Api\KalibrasiAlatController as ApiKalibrasiController;
use App\Http\Controllers\Api\KategoriController as ApiKategoriController;
use App\Http\Controllers\Api\NotificationController ;
use App\Http\Controllers\Api\PengambilanAlatController as ApiPengambilanAlatController;
use App\Http\Controllers\Api\PengambilanSparepartController;
use App\Http\Controllers\Api\PengembalianAlatController as ApiPengembalianAlatController;
use App\Http\Controllers\Api\PengembalianController;
use App\Http\Controllers\Api\PurchaseRequestController;
use App\Http\Controllers\Api\SparepartController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Public (Tanpa Auth)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/{alat_hashid}', [AuthController::class, 'loginWithAlat']); // ✅ GANTI dari sparepart
Route::post('/register', [AuthController::class, 'register']);
Route::post('/auth/google', [AuthController::class, 'googleLoginMobile']);

Route::get('/spareparts/id/{id}', [SparepartController::class, 'showByNumericId'])
    ->name('api.spareparts.showByNumericId');
Route::get('/alats', [ApiAlatController::class, 'index'])->name('alats.index');
/*
|--------------------------------------------------------------------------
| API Routes - Protected (Dengan Auth Sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // ======================== AUTH & PROFILE ========================
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('api.profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('api.profile.update');
    Route::post('/profile/photo', [AuthController::class, 'uploadPhoto'])->name('api.profile.photo');

    // ======================== NOTIFICATIONS ========================

    // Notifikasi
 Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

 Route::get('/admin/dashboard/stats', [DashboardController::class, 'adminStats']);
    Route::get('/karyawan/dashboard/stats', [DashboardController::class, 'karyawanStats']);

    // ======================== BAGIAN ========================
    Route::prefix('bagian')->name('api.bagian.')->group(function () {
        Route::get('/', [BagianController::class, 'index'])->name('index');
        Route::post('/', [BagianController::class, 'store'])->name('store');
        Route::get('/create', [BagianController::class, 'create'])->name('create');
        Route::get('/{hashid}', [BagianController::class, 'show'])->name('show');
        Route::get('/{hashid}/edit', [BagianController::class, 'edit'])->name('edit');
        Route::put('/{hashid}', [BagianController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [BagianController::class, 'destroy'])->name('destroy');
    });

    // ======================== USERS ========================
    Route::prefix('users')->name('api.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // ======================== SPAREPARTS ========================
    Route::prefix('spareparts')->name('api.spareparts.')->group(function () {
        Route::get('/', [SparepartController::class, 'index'])->name('index');
        Route::post('/', [SparepartController::class, 'store'])->name('store');
        Route::get('/create', [SparepartController::class, 'create'])->name('create');
        Route::get('/trashed', [SparepartController::class, 'trashed'])->name('trashed');
        Route::get('/check-stock', [SparepartController::class, 'checkStock'])->name('checkStock');
        Route::get('/generate-all-qr', [SparepartController::class, 'generateAllQrCodes'])->name('generateAllQr');

        Route::get('/{hashid}', [SparepartController::class, 'show'])->name('show');
        Route::get('/{hashid}/edit', [SparepartController::class, 'edit'])->name('edit');
        Route::put('/{hashid}', [SparepartController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [SparepartController::class, 'destroy'])->name('destroy');

        Route::post('/{hashid}/restore', [SparepartController::class, 'restore'])->name('restore');
        Route::delete('/{hashid}/force-delete', [SparepartController::class, 'forceDelete'])->name('forceDelete');
        Route::post('/{hashid}/regenerate-qr', [SparepartController::class, 'regenerateQrCode'])->name('regenerateQr');
    });

    // ======================== KATEGORI ========================
    Route::prefix('kategoris')->name('api.kategoris.')->group(function () {
        Route::get('/', [ApiKategoriController::class, 'index'])->name('index');
        Route::post('/', [ApiKategoriController::class, 'store'])->name('store');
        Route::get('/create', [ApiKategoriController::class, 'create'])->name('create');
        Route::get('/{hashid}', [ApiKategoriController::class, 'show'])->name('show');
        Route::get('/{hashid}/edit', [ApiKategoriController::class, 'edit'])->name('edit');
        Route::put('/{hashid}', [ApiKategoriController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [ApiKategoriController::class, 'destroy'])->name('destroy');
    });

    // ======================== ALAT ========================
    Route::prefix('alats')->name('api.alats.')->group(function () {
        Route::get('/', [ApiAlatController::class, 'index'])->name('index');
        Route::post('/', [ApiAlatController::class, 'store'])->name('store');
        Route::get('/create', [ApiAlatController::class, 'create'])->name('create');
        Route::get('/trashed', [ApiAlatController::class, 'trashed'])->name('trashed');
        Route::get('/daftar-riwayat', [ApiAlatController::class, 'daftarRiwayat'])->name('daftarRiwayat');
        Route::get('/generate-all-qr-codes', [ApiAlatController::class, 'generateAllQrCodes'])->name('generateAllQrCodes');
        Route::get('/export-excel', [ApiAlatController::class, 'exportExcel'])->name('exportExcel');

        Route::get('/{hashid}', [ApiAlatController::class, 'show'])->name('show');
        Route::get('/{hashid}/edit', [ApiAlatController::class, 'edit'])->name('edit');
        Route::get('/{hashid}/riwayat', [ApiAlatController::class, 'riwayat'])->name('riwayat');
        Route::put('/{hashid}', [ApiAlatController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [ApiAlatController::class, 'destroy'])->name('destroy');

        Route::post('/{hashid}/restore', [ApiAlatController::class, 'restore'])->name('restore');
        Route::delete('/{hashid}/force-delete', [ApiAlatController::class, 'forceDelete'])->name('forceDelete');

        Route::get('/{hashid}/kalibrasi', [ApiKalibrasiController::class, 'indexByAlat'])->name('kalibrasi.index');
        Route::post('/{hashid}/kalibrasi', [ApiKalibrasiController::class, 'store'])->name('kalibrasi.store');
    });

    // ======================== KALIBRASI (Global) ========================
    Route::prefix('kalibrasis')->name('api.kalibrasis.')->group(function () {
        Route::get('/', [ApiKalibrasiController::class, 'index'])->name('index');
        Route::get('/create/{hashid}', [ApiKalibrasiController::class, 'create'])->name('create');
        Route::get('/{hashid}', [ApiKalibrasiController::class, 'show'])->name('show');
        Route::get('/{hashid}/edit', [ApiKalibrasiController::class, 'edit'])->name('edit');
        Route::put('/{hashid}', [ApiKalibrasiController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [ApiKalibrasiController::class, 'destroy'])->name('destroy');
    });

    // ======================== PENGAMBILAN ALAT ========================
    Route::prefix('pengambilan_alat')->name('api.pengambilan_alat.')->group(function () {
        Route::get('/', [ApiPengambilanAlatController::class, 'index'])->name('index');
        Route::post('/', [ApiPengambilanAlatController::class, 'store'])->name('store');
        Route::get('/create', [ApiPengambilanAlatController::class, 'create'])->name('create');
        Route::get('/{hashid}', [ApiPengambilanAlatController::class, 'show'])->name('show');
        Route::get('/{hashid}/edit', [ApiPengambilanAlatController::class, 'edit'])->name('edit');
        Route::put('/{hashid}', [ApiPengambilanAlatController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [ApiPengambilanAlatController::class, 'destroy'])->name('destroy');
    });

    // ======================== PENGEMBALIAN ALAT ========================
    Route::prefix('pengembalian_alat')->name('api.pengembalian_alat.')->group(function () {
        Route::get('/', [ApiPengembalianAlatController::class, 'index'])->name('index');
        Route::get('/by-alat/{hashid}', [ApiPengembalianAlatController::class, 'getByAlat'])->name('getByAlat');
        Route::get('/create/{pengambilanHashid}', [ApiPengembalianAlatController::class, 'create'])->name('create');
        Route::post('/{pengambilanHashid}', [ApiPengembalianAlatController::class, 'store'])->name('store');
        Route::get('/{hashid}', [ApiPengembalianAlatController::class, 'show'])->name('show');
        Route::get('/{hashid}/edit', [ApiPengembalianAlatController::class, 'edit'])->name('edit');
        Route::put('/{hashid}', [ApiPengembalianAlatController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [ApiPengembalianAlatController::class, 'destroy'])->name('destroy');
    });

    // ======================== PENGAMBILAN SPAREPART (Legacy) ========================
    Route::prefix('pengambilan')->name('api.pengambilan.')->group(function () {
        Route::get('/', [PengambilanSparepartController::class, 'index'])->name('index');
        Route::post('/', [PengambilanSparepartController::class, 'store'])->name('store');
        Route::get('/create', [PengambilanSparepartController::class, 'create'])->name('create');
        Route::get('/{hashid}', [PengambilanSparepartController::class, 'show'])->name('show');
        Route::get('/{hashid}/edit', [PengambilanSparepartController::class, 'edit'])->name('edit');
        Route::put('/{hashid}', [PengambilanSparepartController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [PengambilanSparepartController::class, 'destroy'])->name('destroy');
    });

    // ======================== PENGEMBALIAN SPAREPART (Legacy) ========================
    Route::prefix('pengembalian')->name('api.pengembalian.')->group(function () {
        Route::get('/', [PengembalianController::class, 'index'])->name('index');
        Route::post('/', [PengembalianController::class, 'store'])->name('store');
        Route::get('/create', [PengembalianController::class, 'create'])->name('create');
        Route::get('/{hashid}', [PengembalianController::class, 'show'])->name('show');
        Route::get('/{hashid}/edit', [PengembalianController::class, 'edit'])->name('edit');
        Route::put('/{hashid}', [PengembalianController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [PengembalianController::class, 'destroy'])->name('destroy');
    });

    // ======================== PURCHASE REQUEST ========================
    Route::prefix('purchase-requests')->name('api.purchase-requests.')->group(function () {
        Route::get('/', [PurchaseRequestController::class, 'index'])->name('index');
        Route::post('/', [PurchaseRequestController::class, 'store'])->name('store');
        Route::get('/create', [PurchaseRequestController::class, 'create'])->name('create');
        Route::get('/{hashid}', [PurchaseRequestController::class, 'show'])->name('show');
        Route::get('/{hashid}/edit', [PurchaseRequestController::class, 'edit'])->name('edit');
        Route::put('/{hashid}', [PurchaseRequestController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [PurchaseRequestController::class, 'destroy'])->name('destroy');

        Route::post('/{hashid}/approve', [PurchaseRequestController::class, 'approve'])->name('approve');
        Route::post('/{hashid}/reject', [PurchaseRequestController::class, 'reject'])->name('reject');
        Route::post('/{hashid}/complete', [PurchaseRequestController::class, 'complete'])->name('complete');
    });
});
