<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

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

// Rute default Laravel untuk mengambil data user yang terotentikasi
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rute baru kita untuk menyediakan data ke grafik di dashboard admin
Route::middleware('auth:sanctum')->get('/stats/mahasiswa-per-prodi', [DashboardController::class, 'mahasiswaPerProdi']);