<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuratIzinController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\ParentChildController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/auth/user', [AuthController::class, 'getUser']);

Route::get('/guru/tugas', [TugasController::class, 'index']);
Route::get('/guru/tugas/{id}', [TugasController::class, 'show']);
Route::post('/guru/tugas', [TugasController::class, 'store']);
Route::put('/guru/tugas/{id}', [TugasController::class, 'update']);
Route::delete('/guru/tugas/{id}', [TugasController::class, 'destroy']);


Route::middleware('auth:sanctum')->post('/parent-child/send', [ParentChildController::class, 'sendRequest']);
Route::middleware('auth:sanctum')->post('/parent-child/confirm/{parent_id}', [ParentChildController::class, 'confirmRequest']);
Route::middleware('auth:sanctum')->get('/parent-child/children', [ParentChildController::class, 'getChildren']);
Route::middleware('auth:sanctum')->get('/parent-child/parents', [ParentChildController::class, 'getParents']);



Route::get('/test', function () {
    return response()->json(['message' => 'Server is running']);
});

Route::middleware('auth:sanctum')->put('/auth/user', [AuthController::class, 'updateUser']);
Route::middleware('auth:sanctum')->post('/auth/user/change-password', [AuthController::class, 'changePassword']);

Route::post('/kelas/tambah', [KelasController::class, 'store']);
Route::middleware('auth:sanctum')->get('/kelas/getkelasuser', [KelasController::class, 'getKelasByUserId']);
Route::middleware('auth:sanctum')->post('/kelas/create', [KelasController::class, 'create']);
Route::middleware('auth:sanctum')->post('/kelas/{id_kelas}/addMataPelajaran', [KelasController::class, 'addMataPelajaran']);
Route::middleware('auth:sanctum')->get('/user/kelas', [KelasController::class, 'getKelasWithMataPelajaran']);


Route::middleware('auth:sanctum')->put('/orangtua/suratizin/{id}', [SuratIzinController::class, 'update']);
Route::middleware('auth:sanctum')->get('/orangtua/suratizin', [SuratIzinController::class, 'index']);
Route::middleware('auth:sanctum')->get('/orangtua/suratizin/{id}', [SuratIzinController::class, 'show']);
Route::middleware('auth:sanctum')->get('/guru/suratizin/{id}', [SuratIzinController::class, 'show']);
Route::middleware('auth:sanctum')->post('/orangtua/suratizin', [SuratIzinController::class, 'store']);
Route::middleware('auth:sanctum')->delete('/orangtua/suratizin/{id}', [SuratIzinController::class, 'destroy']);
Route::middleware('auth:sanctum')->get('/kelas/{id_kelas}/mataPelajaran', [KelasController::class, 'getMataPelajaran']);
Route::middleware('auth:sanctum')->post('/kelas/enroll', [KelasController::class, 'enroll']);


Route::put('/orangtua/suratizin/{id}', [SuratIzinController::class, 'update']);
Route::get('/orangtua/suratizin', [SuratIzinController::class, 'index']);
Route::get('/orangtua/suratizin/{id}', [SuratIzinController::class, 'show']);
Route::get('/guru/suratizin/{id}', [SuratIzinController::class, 'show']);
Route::post('/orangtua/suratizin', [SuratIzinController::class, 'store']);
Route::delete('/orangtua/suratizin/{id}', [SuratIzinController::class, 'destroy']);