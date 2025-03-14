<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PegawaiController;
use Barryvdh\DomPDF\Facade\PDF;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UnitKerjaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);  // Route untuk login
    Route::post('register', [AuthController::class, 'register']); // Route untuk registrasi
    Route::get('/user', [AuthController::class, 'index']);
    Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']); // Route untuk logout, hanya dapat diakses jika sudah login
});

Route::middleware('auth:api')->get('/dashboard', [DashboardController::class, 'index']);

// Rute untuk Pegawai dengan middleware autentikasi dan role admin
Route::prefix('pegawai')->middleware(['auth:api'])->group(function() {
    Route::get('/', [PegawaiController::class, 'index']);         
    Route::post('/', [PegawaiController::class, 'store']);        
    Route::get('{id}', [PegawaiController::class, 'show']);       
    Route::put('{id}', [PegawaiController::class, 'update']);    
    Route::delete('{id}', [PegawaiController::class, 'destroy']);
});

// Rute untuk Unit Kerja dengan middleware autentikasi dan role admin
Route::prefix('unitkerja')->middleware(['auth:api'])->group(function() {
    Route::get('/', [UnitKerjaController::class, 'index']);       
    Route::post('/', [UnitKerjaController::class, 'store']);      
    Route::get('{id}', [UnitKerjaController::class, 'show']);     
    Route::put('{id}', [UnitKerjaController::class, 'update']);   
    Route::delete('{id}', [UnitKerjaController::class, 'destroy']); 
});