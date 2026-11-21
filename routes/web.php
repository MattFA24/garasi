<?php

use App\Http\Controllers\GarageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Halaman admin: harus login dan role-nya admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
    Route::patch('/tenants/{user}/approve', [TenantController::class, 'approve'])->name('tenants.approve');
    Route::patch('/tenants/{user}/reject', [TenantController::class, 'reject'])->name('tenants.reject');

    Route::get('/garages', [GarageController::class, 'index'])->name('garages.index');
    Route::post('/garages', [GarageController::class, 'store'])->name('garages.store');
});

require __DIR__.'/auth.php';
