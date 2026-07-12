
    <?php

use App\Exports\PengambilanExport;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AlatsController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\BagiansController;
use App\Http\Controllers\KalibrasiAlatController;
use App\Http\Controllers\KaryawanDashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PengambilanAlatController;
use App\Http\Controllers\PengambilanSparepartController;
use App\Http\Controllers\PengembalianAlatController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\QrScannerController;
use App\Http\Controllers\Web\SparepartsController;
use App\Http\Controllers\SuperDashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\NotificationController;

    /*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group that
    | contains the "web" middleware group by default.
    |
    */

    Route::get('/', function () {
    return view('landing');
})->name('landing');
Route::get('/test-check-expired', [App\Http\Controllers\AlatsController::class, 'checkExpired']);

    // Login Routes
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/login/{spareparts_hashid}', [AuthController::class, 'showLoginWithSparepart'])
        ->name('login.with-sparepart');
    Route::post('/login/{spareparts_hashid}', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
    Route::get('/auth/google', [GoogleController::class, 'redirects'])->name('google.redirects');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

Route::get('/alats/riwayat-alat', [AlatsController::class, 'daftarRiwayat'])->name('alats.daftarRiwayat');
Route::post('/onboarding/complete', function () {
    $user = auth::user();

    if (!$user) {
        return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
    }

    $user->forceFill([
        'onboarding_completed' => true
    ])->save();

    return response()->json(['status' => 'success']);
})->middleware('auth')->name('onboarding.complete');
Route::get('/scan-qr', [QrScannerController::class, 'index'])->name('qr-scanner');
Route::post('/scan-qr/process', [QrScannerController::class, 'process'])->name('qr-scanner.process');
    // Encrypted Redirect Routes
    // Route::prefix('redirect')->name('redirect.')->group(function () {
    //     Route::get('/sparepart/{encryptedId}', [SparepartsController::class, 'redirectDecrypt'])->name('sparepart');
    //     Route::get('/generate/{id}', [SparepartsController::class, 'generateEncryptedLink'])->name('generate');
    // });
Route::get('/alats', [AlatsController::class, 'index'])->name('alats.index');

Route::get('/alats/{hashid}', [AlatsController::class, 'show'])->name('alats.show');
    // Protected Routes with Auth Middleware
    Route::middleware(['auth'])->group(function () {


// ✨ Routes untuk notifikasi navbar
Route::middleware(['auth'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
});
Route::post('/notifications/mark-all-read', function() {
    Auth::user()->unreadNotifications->markAsRead();
    return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
})->name('notifications.markAllRead');



Route::post('/notifications/{id}/mark-read', function ($id) {
    $notification = Auth::user()->notifications()->findOrFail($id);
    $notification->markAsRead();
    return response()->json(['success' => true, 'message' => 'Notification marked as read']);
})->name('notifications.markRead');

        Route::get('/sparepart/log/{id}', function ($id) {
            return view('sparepart-log-page', ['id' => $id]);
        });

        Route::get('/pengambilan/export/{id?}', function ($id = null) {
            return Excel::download(new PengambilanExport($id), 'pengambilan_' . ($id ? 'id_' . $id : 'all') . '.xlsx');
        })->name('pengambilan.export');
        // Dashboard Routes
        Route::get('/karyawan/dashboard', [KaryawanDashboardController::class, 'index'])
            ->middleware('role:karyawan')
            ->name('karyawan.dashboard');

        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
            ->middleware('role:admin')
            ->name('admin.dashboard');

        Route::get('/super/dashboard', [SuperDashboardController::class, 'index'])
            ->middleware('role:super')
            ->name('super.dashboard');

        // Sparepart Routes
        // Route::prefix('sparepart')->name('sparepart.')->group(function () {
        //     Route::get('/', [SparepartsController::class, 'index'])->name('index');
        //     Route::get('/check-stock', [SparepartsController::class, 'checkStock'])->name('checkstock');
        //     Route::get('/{hashid}', [SparepartsController::class, 'show'])->name('show');


        //     Route::get('/trashed', [SparepartsController::class, 'trashed'])->name('trashed');
        //     Route::middleware('role:admin,super')->group(function () {

        //         Route::get('/create', [SparepartsController::class, 'create'])->name('create');
        //         Route::post('/', [SparepartsController::class, 'store'])->name('store');
        //         Route::get('/unduh', [SparepartsController::class, 'unduh'])->name('unduh');
        //         Route::get('/pdf/{hashid}', [SparepartsController::class, 'downloadPdf'])->name('pdf');

        //         Route::get('/{hashid}/edit', [SparepartsController::class, 'edit'])->name('edit');
        //         Route::put('/{hashid}', [SparepartsController::class, 'update'])->name('update');

        //         Route::delete('/{hashid}', [SparepartsController::class, 'destroy'])->name('destroy');
        //         Route::get('/{hashid}/regenerate-qr', [SparepartsController::class, 'regenerateQrCode'])->name('regenerateQrCode');
        //         Route::get('/trashed', [SparepartsController::class, 'trashed'])->name('trashed');
        //         Route::post('/{hashid}/restore', [SparepartsController::class, 'restore'])->name('restore');
        //         Route::delete('/{hashid}/force-delete', [SparepartsController::class, 'forceDelete'])->name('forceDelete');
        //         Route::get('/qr', [SparepartsController::class, 'generateAllQrCodes']);
        //         Route::get('/v2qr', [SparepartsController::class, 'generateQrCode']);
        //         Route::get('/generate-all-qr', [SparepartsController::class, 'generateAllQrCodes']);
        //     });
        // });
          // Pengambilan Sparepart Routes
        Route::prefix('pengambilan')->name('pengambilan.')->group(function () {
            Route::get('/', [PengambilanSparepartController::class, 'index'])->name('index');
            Route::get('/create', [PengambilanSparepartController::class, 'create'])->name('create');
            Route::get('/create/{spareparts_id}', [PengambilanSparepartController::class, 'create'])->name('create.with-sparepart');
            Route::post('/', [PengambilanSparepartController::class, 'store'])->name('store');
            Route::get('/export-pdf/{id?}', [PengambilanSparepartController::class, 'exportPdf'])->name('exportpdf');

            // ID based routes
            Route::get('/{id}', [PengambilanSparepartController::class, 'show'])->name('show');
            Route::get('/{pengambilanSparepart}/edit', [PengambilanSparepartController::class, 'edit'])->name('edit');
            Route::put('/{pengambilanSparepart}', [PengambilanSparepartController::class, 'update'])->name('update');
            Route::delete('/{pengambilanSparepart}', [PengambilanSparepartController::class, 'destroy'])->name('destroy');
        });
        Route::prefix('pengembalian')->name('pengembalian.')->group(function () {
            Route::get('/', [PengembalianController::class, 'index'])->name('index');
            Route::get('/create', [PengembalianController::class, 'create'])->name('create');
            Route::post('/', [PengembalianController::class, 'store'])->name('store');
            // ID based routes
            Route::get('/{hashid}', [PengembalianController::class, 'show'])->name('show');
            Route::get('/{hashid}/edit', [PengembalianController::class, 'edit'])->name('edit');
            Route::put('/{hashid}', [PengembalianController::class, 'update'])->name('update');
            Route::delete('/{hashid}', [PengembalianController::class, 'destroy'])->name('destroy');
        });

        // Purchase Request Routes
        Route::prefix('purchase-requests')->name('purchase_requests.')->group(function () {
            Route::get('/', [PurchaseRequestController::class, 'index'])->name('index');
            Route::get('/{hashid}', [PurchaseRequestController::class, 'show'])->name('show');


            Route::middleware('role:admin,super')->group(function () {
                Route::get('/create', [PurchaseRequestController::class, 'create'])->name('create');
                Route::post('/', [PurchaseRequestController::class, 'store'])->name('store');
                Route::get('/{hashid}/edit', [PurchaseRequestController::class, 'edit'])->name('edit');
                Route::get('/unduh', [PurchaseRequestController::class, 'unduh'])->name('unduh');
                Route::put('/{hashid}', [PurchaseRequestController::class, 'update'])->name('update');
                Route::delete('/{hashid}', [PurchaseRequestController::class, 'destroy'])->name('destroy');
                Route::post('/{hashid}/approve', [PurchaseRequestController::class, 'approve']);
                Route::post('/{hashid}/reject', [PurchaseRequestController::class, 'reject']);
                Route::post('/{hashid}/complete', [PurchaseRequestController::class, 'complete']);
            });
        });
   Route::prefix('alats')->name('alats.')->middleware('auth')->group(function () {

    // ================= STATIC ROUTES (DI ATAS) =================
    Route::get('/', [AlatsController::class, 'index'])->name('index');
    Route::get('/trashed', [AlatsController::class, 'trashed'])->name('trashed');

    // ✅ PENTING: Route statis harus DI ATAS route dinamis
    Route::get('/riwayat-alat', [AlatsController::class, 'daftarRiwayat'])->name('daftarRiwayat');

    // ================= ADMIN =================
    Route::middleware('role:admin,super')->group(function () {
        Route::get('/create', [AlatsController::class, 'create'])->name('create');
        Route::post('/', [AlatsController::class, 'store'])->name('store');
    });

    // ================= ROUTE DENGAN PARAMETER {hashid} =================
    Route::middleware('role:admin,super')->group(function () {
        Route::get('/{hashid}/riwayat', [AlatsController::class, 'riwayat'])->name('riwayat');
        Route::get('/{hashid}/edit', [AlatsController::class, 'edit'])->name('edit');
        Route::put('/{hashid}', [AlatsController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [AlatsController::class, 'destroy'])->name('destroy');
        Route::post('/{hashid}/restore', [AlatsController::class, 'restore'])->name('restore');
        Route::delete('/{hashid}/force-delete', [AlatsController::class, 'forceDelete'])->name('forceDelete');
    });

    // 🔥 ROUTE SHOW DI PALING BAWAH
    Route::get('/{hashid}', [AlatsController::class, 'show'])->name('show');
});
    Route::prefix('kalibrasis')->name('kalibrasis.')->middleware(['auth','role:admin,super'])->group(function () {

    Route::get('/', [KalibrasiAlatController::class, 'index'])->name('index');
    Route::get('/create/{hashid}', [KalibrasiAlatController::class, 'create'])->name('create');
    Route::post('/store/{hashid}', [KalibrasiAlatController::class, 'store'])->name('store');
    Route::get('/{hashid}', [KalibrasiAlatController::class, 'show'])->name('show');
    Route::get('/{hashid}/edit', [KalibrasiAlatController::class, 'edit'])->name('edit');
    Route::put('/{hashid}', [KalibrasiAlatController::class, 'update'])->name('update');
    Route::delete('/{hashid}', [KalibrasiAlatController::class, 'destroy'])->name('destroy');
});
    Route::prefix('pengambilan_alat')->name('pengambilan_alat.')->group(function () {

            // ================= PUBLIC (login user) =================
            Route::get('/', [PengambilanAlatController::class, 'index'])->name('index');
            Route::get('/create', [PengambilanAlatController::class, 'create'])->name('create');
            Route::post('/', [PengambilanAlatController::class, 'store'])->name('store');
            Route::get('/{hashid}', [PengambilanAlatController::class, 'show'])->name('show');
            // ================= EXPORT =================
            Route::get('/export/pdf', [PengambilanAlatController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/pdf/{hashid}', [PengambilanAlatController::class, 'exportPdf'])->name('export.single');
            // ================= ADMIN ONLY =================
            Route::middleware('role:admin,super')->group(function () {
                Route::get('/{hashid}/edit', [PengambilanAlatController::class, 'edit'])->name('edit');
                Route::put('/{hashid}', [PengambilanAlatController::class, 'update'])->name('update');
                Route::delete('/{hashid}', [PengambilanAlatController::class, 'destroy'])->name('destroy');

            });
        });
        Route::prefix('pengembalian_alat')->name('pengembalian_alat.')->group(function () {

            Route::get('/', [PengembalianAlatController::class, 'index'])->name('index');
            Route::get('/create/{hashid}', [PengembalianAlatController::class, 'create'])->name('create');
            Route::post('/store/{hashid}', [PengembalianAlatController::class, 'store'])->name('store');
            Route::get('/{hashid}', [PengembalianAlatController::class, 'show'])->name('show');
            Route::get('/{hashid}/edit', [PengembalianAlatController::class, 'edit'])->name('edit');
            Route::put('/{hashid}', [PengembalianAlatController::class, 'update'])->name('update');
            Route::delete('/{hashid}', [PengembalianAlatController::class, 'destroy'])->name('destroy');
            Route::get('/export/pdf', [PengembalianAlatController::class, 'exportPdf'])->name('export');
             Route::get('/export/pdf/{hashid}', [PengembalianAlatController::class, 'exportPdf'])->name('export.single');
        });

    Route::prefix('kategoris')->name('kategoris.')->middleware(['auth','role:admin,super'])->group(function () {

    Route::get('/', [KategoriController::class,'index'])->name('index');

    // 🔥 WAJIB DI ATAS
    Route::get('/create', [KategoriController::class,'create'])->name('create');
    Route::post('/', [KategoriController::class,'store'])->name('store');

    Route::get('/{hashid}', [KategoriController::class, 'show'])->name('show');
    Route::get('/{hashid}/edit', [KategoriController::class,'edit'])->name('edit');
    Route::put('/{hashid}', [KategoriController::class,'update'])->name('update');
    Route::delete('/{hashid}', [KategoriController::class,'destroy'])->name('destroy');

});




        // User Management Routes
            Route::prefix('anggota')->name('admin.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');

            // User based routes
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });

        // Bagian Routes
        Route::middleware('role:admin,super')->prefix('bagian')->name('bagian.')->group(function () {
            Route::get('/', [BagiansController::class, 'index'])->name('index');
            Route::get('/create', [BagiansController::class, 'create'])->name('create');
            Route::post('/', [BagiansController::class, 'store'])->name('store');

            // Bagian based routes
            Route::get('/{bagian}/edit', [BagiansController::class, 'edit'])->name('edit');
            Route::put('/{bagian}', [BagiansController::class, 'update'])->name('update');
            Route::delete('/{bagian}', [BagiansController::class, 'destroy'])->name('destroy');
        });
    });
