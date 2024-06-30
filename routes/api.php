<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuratIzinController;
use App\Http\Controllers\TugasController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/guru/tugas', [TugasController::class, 'index']);
Route::get('/guru/tugas/{id}', [TugasController::class, 'show']);
Route::post('/guru/tugas', [TugasController::class, 'store']);
Route::put('/guru/tugas/{id}', [TugasController::class, 'update']);
Route::delete('/guru/tugas/{id}', [TugasController::class, 'destroy']);

Route::put('/orangtua/suratizin/{id}', [SuratIzinController::class, 'update']);
Route::get('/orangtua/suratizin', [SuratIzinController::class, 'index']);
Route::get('/orangtua/suratizin/{id}', [SuratIzinController::class, 'show']);
Route::get('/guru/suratizin/{id}', [SuratIzinController::class, 'show']);
Route::post('/orangtua/suratizin', [SuratIzinController::class, 'store']);
Route::delete('/orangtua/suratizin/{id}', [SuratIzinController::class, 'destroy']);