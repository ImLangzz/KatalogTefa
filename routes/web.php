<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProductReportController;

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/katalog/{product}', [CatalogController::class, 'show'])->name('catalog.show');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/katalog-baru', [CatalogController::class, 'create'])->name('catalog.create');
    Route::post('/katalog', [CatalogController::class, 'store'])->name('catalog.store');
    Route::get('/katalog/{product}/edit', [CatalogController::class, 'edit'])->name('catalog.edit');
    Route::put('/katalog/{product}', [CatalogController::class, 'update'])->name('catalog.update');
    Route::delete('/katalog/{product}', [CatalogController::class, 'destroy'])->name('catalog.destroy');
    Route::post('/katalog/{product}/laporkan', [ProductReportController::class, 'store'])->name('reports.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::patch('/laporan/{report}', [AdminController::class, 'updateReport'])->name('reports.update');
    Route::delete('/katalog/{product}', [AdminController::class, 'destroyProduct'])->name('products.destroy');
});
