<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Cashier\PosController;
use App\Http\Controllers\Cashier\ReceiptController;
use App\Http\Controllers\Cashier\TransactionController as CashierTransactionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Manager\DashboardController;
use App\Http\Controllers\Manager\MenuController;
use App\Http\Controllers\Manager\TransactionController as ManagerTransactionController;
use App\Http\Controllers\Manager\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Panel Kasir — kasir, manajer, superadmin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:kasir,manager,superadmin'])
    ->prefix('kasir')
    ->name('cashier.')
    ->group(function () {
        Route::get('/transaksi', [CashierTransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transaksi/baru', [PosController::class, 'create'])->name('pos.create');
        Route::post('/transaksi', [PosController::class, 'store'])->name('transactions.store');
        Route::get('/transaksi/{transaction}', [CashierTransactionController::class, 'show'])->name('transactions.show');
        Route::post('/transaksi/{transaction}/bayar', [CashierTransactionController::class, 'pay'])->name('transactions.pay');
        Route::delete('/transaksi/{transaction}', [CashierTransactionController::class, 'destroy'])->name('transactions.destroy');
        Route::get('/transaksi/{transaction}/struk', ReceiptController::class)->name('transactions.receipt');
    });

/*
|--------------------------------------------------------------------------
| Panel Manajer — manajer & superadmin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:manager,superadmin'])
    ->prefix('manajer')
    ->name('manager.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::get('/transaksi', [ManagerTransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transaksi/{transaction}', [ManagerTransactionController::class, 'show'])->name('transactions.show');

        Route::get('/menu', [MenuController::class, 'index'])->name('menus.index');
        Route::post('/menu', [MenuController::class, 'store'])->name('menus.store');
        Route::get('/menu/{menu}', [MenuController::class, 'show'])->name('menus.show');
        Route::put('/menu/{menu}', [MenuController::class, 'update'])->name('menus.update');
        Route::delete('/menu/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');

        Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');
        Route::post('/pengguna', [UserController::class, 'store'])->name('users.store');
        Route::get('/pengguna/{user}', [UserController::class, 'show'])->name('users.show');
        Route::put('/pengguna/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/pengguna/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
