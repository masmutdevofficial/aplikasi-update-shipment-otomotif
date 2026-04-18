<?php

use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VendorController;
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
Route::middleware('auth')->group(function () {
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
            Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');

            // User Management
            Route::resource('users', UserController::class)->except(['show']);
            Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

            // Vendor Management
            Route::resource('vendors', VendorController::class)->except(['show']);

            // Shipment Management
            Route::resource('shipments', ShipmentController::class)->except(['show']);

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
    Route::middleware([CheckLevel::class . ':vendor', CheckVendorStatus::class])
        ->prefix('vendor')
        ->name('vendor.')
        ->group(function () {
            Route::get('/dashboard', VendorDashboardController::class)->name('dashboard');
            Route::get('/scanner', [ScannerController::class, 'index'])->name('scanner');
            Route::post('/scanner/scan', [ScannerController::class, 'scan'])->name('scanner.scan');
            Route::post('/scanner/confirm', [ScannerController::class, 'confirm'])->name('scanner.confirm');
            Route::get('/history', HistoryController::class)->name('history');

            // Documentation
            Route::get('/docs/user-guide', fn () => view('vendor.docs.user-guide'))->name('docs.user-guide');
        });
});
