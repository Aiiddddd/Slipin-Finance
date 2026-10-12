<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\PolycupController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\OwnerOnly;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Sesi jualan
    Route::get('/sesi', [SessionController::class, 'index'])->name('sesi.index');
    Route::get('/sesi/buka', [SessionController::class, 'create'])->name('sesi.create');
    Route::post('/sesi', [SessionController::class, 'store'])->name('sesi.store');
    Route::get('/sesi/{id}', [SessionController::class, 'show'])->whereNumber('id')->name('sesi.show');
    Route::put('/sesi/{id}', [SessionController::class, 'update'])->whereNumber('id')->name('sesi.update');
    Route::post('/sesi/{id}/items', [SessionController::class, 'saveItems'])->whereNumber('id')->name('sesi.items');
    Route::get('/sesi/{id}/tutup', [SessionController::class, 'closeForm'])->whereNumber('id')->name('sesi.closeForm');
    Route::post('/sesi/{id}/tutup', [SessionController::class, 'close'])->whereNumber('id')->name('sesi.close');

    // Pengeluaran
    Route::get('/pengeluaran', [ExpenseController::class, 'index'])->name('pengeluaran.index');
    Route::get('/pengeluaran/tambah', [ExpenseController::class, 'create'])->name('pengeluaran.create');
    Route::post('/pengeluaran', [ExpenseController::class, 'store'])->name('pengeluaran.store');
    Route::get('/pengeluaran/{id}/edit', [ExpenseController::class, 'edit'])->whereNumber('id')->name('pengeluaran.edit');
    Route::put('/pengeluaran/{id}', [ExpenseController::class, 'update'])->whereNumber('id')->name('pengeluaran.update');

    // Polycup (semua bisa lihat stok)
    Route::get('/polycup', [PolycupController::class, 'index'])->name('polycup.index');

    // ---- Khusus owner ----
    Route::middleware(OwnerOnly::class)->group(function () {
        Route::post('/sesi/{id}/buka-lagi', [SessionController::class, 'reopen'])->whereNumber('id')->name('sesi.reopen');
        Route::delete('/sesi/{id}', [SessionController::class, 'destroy'])->whereNumber('id')->name('sesi.destroy');

        Route::delete('/pengeluaran/{id}', [ExpenseController::class, 'destroy'])->whereNumber('id')->name('pengeluaran.destroy');

        Route::post('/polycup', [PolycupController::class, 'store'])->name('polycup.store');
        Route::delete('/polycup/{id}', [PolycupController::class, 'destroy'])->whereNumber('id')->name('polycup.destroy');

        Route::get('/laporan', [ReportController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export', [ReportController::class, 'export'])->name('laporan.export');

        Route::get('/master', [MasterController::class, 'index'])->name('master.index');
        Route::post('/master/{type}', [MasterController::class, 'store'])->name('master.store');
        Route::put('/master/{type}/{id}', [MasterController::class, 'update'])->whereNumber('id')->name('master.update');

        Route::get('/user', [UserController::class, 'index'])->name('user.index');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');
        Route::put('/user/{id}', [UserController::class, 'update'])->whereNumber('id')->name('user.update');
    });
});
