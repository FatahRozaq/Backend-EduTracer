<?php

use Illuminate\Http\Request;
use App\Http\Middleware\SetTimezone;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\AbsensiController;


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


Route::middleware([SetTimezone::class])->group(function () {
    Route::get('/absensi/jadwal/kelas/{classId}', [AbsensiController::class, 'getAvailableSchedules']);
    Route::get('/attendance/{classId}', [AbsensiController::class, 'getAttendance']);
    Route::post('/mark-attendance/{classId}', [AbsensiController::class, 'markAttendance']);
    Route::put('/absensi/siswa/update/{classId}', [AbsensiController::class, 'updateAttendance']);
    Route::get('/absensi/kelas/daftar-user/{classId}', [AbsensiController::class, 'getStudentAvailabeAttendances']);
    Route::get('/absensi/status/siswa', [AbsensiController::class, 'getAttendanceStudentStatus']);
    Route::get('/absensi/status/mapel', [AbsensiController::class, 'getAttendanceStatus']);
    Route::get('/kelas/get', [AbsensiController::class, 'getKelas']);
});

// Route::get('/classes/{classId}/attendance', [AbsensiController::class, 'getClassAttendance']);
// Route::get('/students/{studentId}/attendance', [AbsensiController::class, 'getStudentAttendance']);


