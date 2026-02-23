<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MasterDataController;
use App\Http\Middleware\CheckRole;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/pengumuman', [MasterDataController::class, 'indexPengumuman']);

    Route::get('/laporan/{id}', [LaporanController::class, 'show']); 

    Route::middleware(CheckRole::class.':operator_sekolah')->group(function () {
        Route::get('/operator/dashboard', [LaporanController::class, 'index']);
        Route::post('/laporan', [LaporanController::class, 'store']); 
        
        Route::put('/laporan/{id}/siswa', [LaporanController::class, 'updateSiswa']);
        Route::put('/laporan/{id}/rekap-personal', [LaporanController::class, 'updateRekapPersonal']);
        Route::put('/laporan/{id}/guru', [LaporanController::class, 'updateGuru']);
        Route::put('/laporan/{id}/sarpras', [LaporanController::class, 'updateSarpras']);
        Route::put('/laporan/{id}/mobiler', [LaporanController::class, 'updateMobiler']);
        Route::put('/laporan/{id}/keuangan', [LaporanController::class, 'updateKeuangan']);
        
        Route::post('/laporan/{id}/submit', [LaporanController::class, 'submit']);
        Route::delete('/laporan/{id}', [LaporanController::class, 'destroy']);
        Route::post('/laporan/{id}/restore', [LaporanController::class, 'restore']);
        Route::delete('/laporan/{id}/permanent', [LaporanController::class, 'permanentDelete']);
        
        Route::get('/operator/madrasah', [MasterDataController::class, 'showMyMadrasah']);
        Route::put('/operator/madrasah', [MasterDataController::class, 'updateMyMadrasah']);
    });

    Route::middleware(CheckRole::class.':kasi_penmad')->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard']); 
        Route::get('/admin/laporan', [AdminController::class, 'index']); 
        Route::post('/admin/laporan/{id}/verify', [AdminController::class, 'verify']); 
        Route::get('/admin/recap', [AdminController::class, 'recap']);  
        Route::delete('/admin/laporan/{id}', [AdminController::class, 'destroy']);
        Route::post('/admin/laporan/{id}/restore', [AdminController::class, 'restore']);
        Route::delete('/admin/laporan/{id}/permanent', [AdminController::class, 'permanentDelete']);
        
        Route::get('/master/madrasah', [MasterDataController::class, 'indexMadrasah']);
        Route::post('/master/madrasah', [MasterDataController::class, 'storeMadrasah']);
        Route::get('/master/madrasah/{id}', [MasterDataController::class, 'showMadrasah']);
        Route::put('/master/madrasah/{id}', [MasterDataController::class, 'updateMadrasah']);
        Route::delete('/master/madrasah/{id}', [MasterDataController::class, 'destroyMadrasah']);
        
        Route::get('/master/users', [MasterDataController::class, 'indexUsers']);
        Route::post('/master/users', [MasterDataController::class, 'storeUser']);
        Route::put('/master/users/{id}', [MasterDataController::class, 'updateUser']);
        Route::delete('/master/users/{id}', [MasterDataController::class, 'destroyUser']);
        
        Route::post('/master/pengumuman', [MasterDataController::class, 'storePengumuman']);
        Route::delete('/master/pengumuman/{id}', [MasterDataController::class, 'destroyPengumuman']);
      });
});
