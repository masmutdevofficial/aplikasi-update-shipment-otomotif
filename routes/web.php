<?php

use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\SpecialShipmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\PendingVinController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\HistoryController;
use App\Http\Controllers\Vendor\ScannerController;
use App\Http\Middleware\CheckLevel;
use App\Http\Middleware\CheckVendorStatus;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::view('/contact', 'public.contact')->name('contact');
Route::view('/kontak', 'public.contact')->name('kontak');
Route::view('/privacy', 'public.privacy')->name('privacy');
Route::view('/kebijakan-privasi', 'public.privacy')->name('kebijakan-privasi');

Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    // Forgot & Reset Password (guest only)
    Route::get('/password/forgot', [PasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/password/forgot', [PasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/password/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [PasswordController::class, 'resetPassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', CheckVendorStatus::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Change Password (authenticated)
    Route::get('/password/change', [PasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [PasswordController::class, 'changePassword'])->name('password.change.update');

    /*
    |----------------------------------------------------------------------
    | Admin & Superadmin Routes
    |----------------------------------------------------------------------
    */
    Route::middleware(CheckLevel::class . ':superadmin,admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/dashboard', DashboardController::class)->name('dashboard');
            Route::get('/dashboard/alerts', [DashboardController::class, 'alerts'])->name('dashboard.alerts');

            // Application Settings
            Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
            Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

            // User Management
            Route::delete('/users/bulk-delete', [UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
            Route::resource('users', UserController::class)->except(['show']);
            Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

            // Vendor Management
            Route::resource('vendors', VendorController::class)->except(['show']);

            // Shipment Management
            Route::match(['get', 'post'], '/shipments/data', [ShipmentController::class, 'data'])->name('shipments.data');
            Route::delete('/shipments/bulk-delete', [ShipmentController::class, 'bulkDestroy'])->name('shipments.bulk-destroy');
            Route::resource('shipments', ShipmentController::class)->except(['show']);
            Route::get('/shipments/import', [ShipmentController::class, 'showImport'])->name('shipments.import.form');
            Route::post('/shipments/import', [ShipmentController::class, 'importExcel'])->name('shipments.import');
            Route::get('/shipments/template', [ShipmentController::class, 'downloadTemplate'])->name('shipments.template');

            // TSO & ISO Shipment Management (halaman dan query terpisah)
            Route::prefix('shipment-data/{type}')
                ->name('special-shipments.')
                ->where(['type' => 'tso|iso-darat|iso-laut'])
                ->group(function () {
                    Route::get('/', [SpecialShipmentController::class, 'index'])->name('index');
                    // POST keeps the large ISO Laut DataTables payload out of the URL.
                    // GET remains available for backwards compatibility and direct API tests.
                    Route::match(['get', 'post'], '/data', [SpecialShipmentController::class, 'data'])->name('data');
                    Route::get('/create', [SpecialShipmentController::class, 'create'])->name('create');
                    Route::post('/', [SpecialShipmentController::class, 'store'])->name('store');
                    Route::delete('/bulk-delete', [SpecialShipmentController::class, 'bulkDestroy'])->name('bulk-destroy');
                    Route::get('/import', [SpecialShipmentController::class, 'showImport'])->name('import.form');
                    Route::post('/import', [SpecialShipmentController::class, 'import'])->name('import');
                    Route::get('/template', [SpecialShipmentController::class, 'template'])->name('template');
                    Route::get('/{shipment}/edit', [SpecialShipmentController::class, 'edit'])->name('edit');
                    Route::put('/{shipment}', [SpecialShipmentController::class, 'update'])->name('update');
                    Route::delete('/{shipment}', [SpecialShipmentController::class, 'destroy'])->name('destroy');
                });
            Route::get('/pending-vins', [PendingVinController::class, 'index'])->name('pending-vins.index');

            // Reports
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

            // Documentation
            Route::get('/docs/tsd', fn () => view('admin.docs.tsd'))->name('docs.tsd');
            Route::get('/docs/user-guide-admin', fn () => view('admin.docs.user-guide-admin'))->name('docs.user-guide-admin');
            Route::get('/docs/user-guide-vendor', fn () => view('admin.docs.user-guide-vendor'))->name('docs.user-guide-vendor');
        });

    /*
    |----------------------------------------------------------------------
    | Vendor Routes
    |----------------------------------------------------------------------
    */
    Route::middleware(CheckLevel::class . ':vendor')
        ->prefix('vendor')
        ->name('vendor.')
        ->group(function () {
            Route::get('/dashboard', VendorDashboardController::class)->name('dashboard');
            Route::get('/scanner', [ScannerController::class, 'index'])->name('scanner');
            Route::post('/scanner/scan', [ScannerController::class, 'scan'])->name('scanner.scan');
            Route::post('/scanner/confirm', [ScannerController::class, 'confirm'])->name('scanner.confirm');
            Route::get('/history', HistoryController::class)->name('history');
            Route::post('/history/{history}/document', [HistoryController::class, 'uploadDocument'])->name('history.document.upload');

            // Documentation
            Route::get('/docs/user-guide', fn () => view('vendor.docs.user-guide'))->name('docs.user-guide');
        });
});
