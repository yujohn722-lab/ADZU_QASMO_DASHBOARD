<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ElectricityConsumptionController;
use App\Http\Controllers\EstimatedSavingController;
use App\Http\Controllers\FuelPriceController;
use App\Http\Controllers\FuelVehicleUseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SolarPerformanceController;
use App\Http\Controllers\StudentServiceVolumeController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware(['auth', 'no-cache'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('fuel-prices', FuelPriceController::class);
    Route::resource('electricity-consumptions', ElectricityConsumptionController::class);
    Route::resource('fuel-vehicle-uses', FuelVehicleUseController::class);
    Route::resource('solar-performances', SolarPerformanceController::class);
    Route::resource('student-service-volumes', StudentServiceVolumeController::class);
    Route::resource('estimated-savings', EstimatedSavingController::class);

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');

    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
});
