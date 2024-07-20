<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuratIzinController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\ParentChildController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MataPelajaranController;
use App\Http\Controllers\TugasKelasMataPelajaranController;
use Illuminate\Support\Facades\Auth;
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


//user
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/auth/user', [AuthController::class, 'getUser']);
Route::middleware('auth:sanctum')->put('/auth/user', [AuthController::class, 'updateUser']);
Route::middleware('auth:sanctum')->post('/auth/user/change-password', [AuthController::class, 'changePassword']);


//tugas
Route::get('/guru/tugas', [TugasController::class, 'index']);
Route::get('/guru/tugas/{id}', [TugasController::class, 'show']);
Route::post('/guru/tugas', [TugasController::class, 'store']);
Route::put('/guru/tugas/{id}', [TugasController::class, 'update']);
Route::delete('/guru/tugas/{id}', [TugasController::class, 'destroy']);



//Surat Izin
Route::middleware('auth:sanctum')->put('/orangtua/suratizin/{id}', [SuratIzinController::class, 'update']);
Route::middleware('auth:sanctum')->get('/orangtua/suratizin', [SuratIzinController::class, 'index']);
Route::middleware('auth:sanctum')->get('/orangtua/suratizin/{id}', [SuratIzinController::class, 'show']);
Route::middleware('auth:sanctum')->get('/guru/suratizin/{id}', [SuratIzinController::class, 'show']);
Route::middleware('auth:sanctum')->post('/orangtua/suratizin', [SuratIzinController::class, 'store']);
Route::middleware('auth:sanctum')->delete('/orangtua/suratizin/{id}', [SuratIzinController::class, 'destroy']);


//Kelas dan mata pelajaran
Route::middleware('auth:sanctum')->post('/kelas/enroll', [KelasController::class, 'enroll']); //enroll kelas
Route::post('/kelas/tambah', [KelasController::class, 'store']);
Route::middleware('auth:sanctum')->delete('/kelas/{id_kelas}', [KelasController::class, 'destroy']);//menghapus kelas dan kelas mata pelajaran
Route::middleware('auth:sanctum')->put('/kelas/{id_kelas}', [KelasController::class, 'update']);// update kelas

Route::middleware('auth:sanctum')->get('/kelas/getkelasuser', [KelasController::class, 'getKelasByUserId']); //mengambil kelas sesuai id user
Route::middleware('auth:sanctum')->post('/kelas/create', [KelasController::class, 'create']);
Route::middleware('auth:sanctum')->post('/kelas/{id_kelas}/addMataPelajaran', [KelasController::class, 'addMataPelajaran']);// add mata pelajaran ke kelas
Route::middleware('auth:sanctum')->get('/kelas/{id_kelas}/mata-pelajaran', [KelasController::class, 'getMataPelajaran']); //menampilkan mata pelajaran dalam suatu kelas
Route::middleware('auth:sanctum')->delete('/kelas-user/{id_kelas}', [KelasController::class, 'destroyKelasUser']);// menghapus kelas user
Route::middleware('auth:sanctum')->post('/kelas/search', [KelasController::class, 'searchKelas']);

// Route::middleware('auth:sanctum')->post('/kelas/{id_kelas}/mata-pelajaran', [KelasController::class, 'storeMataPelajaran']);
Route::middleware('auth:sanctum')->post('/mapel/{id_kelas}/create', [MataPelajaranController::class, 'storeMataPelajaran']); // membuat mata pelajaran dan langsung menghubungkan ke kelas
Route::middleware('auth:sanctum')->delete('/kelas/{id_kelas}/mata-pelajaran/{id_mata_pelajaran}', [MataPelajaranController::class, 'destroyKelasMataPelajaran']);// menghapus kelas mata pelajaran
Route::middleware('auth:sanctum')->get('/mata-pelajaran/search', [MataPelajaranController::class, 'searchMataPelajaran']); //mencari kelas
Route::middleware('auth:sanctum')->put('/mata-pelajaran/update/{id_mata_pelajaran}', [MataPelajaranController::class, 'updateMataPelajaran']);
Route::middleware('auth:sanctum')->delete('/mata-pelajaran/destroy/{id_mata_pelajaran}', [MataPelajaranController::class, 'destroyMataPelajaran']);



Route::middleware('auth:sanctum')->get('/tugas-kelas-mata-pelajaran', [TugasKelasMataPelajaranController::class, 'getTugasByUser']);
Route::middleware('auth:sanctum')->post('/tugas-kelas-mata-pelajaran/search', [TugasKelasMataPelajaranController::class, 'searchTugas']);
Route::middleware('auth:sanctum')->put('/tugas-kelas-mata-pelajaran/update/{id}', [TugasKelasMataPelajaranController::class, 'updateTugasKelasMataPelajaran']);





// parent child
Route::middleware('auth:sanctum')->get('/user/search-students', [UserController::class, 'searchStudentsByName']);// mencari user siswa dengan key word nama
Route::middleware('auth:sanctum')->get('/users/siswa', [UserController::class, 'getAllSiswa']);// mengambil user siswa yang belum ada si parent_child

Route::middleware('auth:sanctum')->post('/confirm-request', [ParentChildController::class, 'confirmRequest']); //confirm request oleh siswa
Route::middleware('auth:sanctum')->post('/send-request', [ParentChildController::class, 'sendRequest']); //send request ke siswa
Route::middleware('auth:sanctum')->get('/pending-requests', [ParentChildController::class, 'getPendingRequestsForChild']);// mendapatkan request yang pending di siswa

Route::middleware('auth:sanctum')->get('/parent-child/children', [ParentChildController::class, 'getChildren']);// mendapatkan data anak yang sudah konfirm
Route::middleware('auth:sanctum')->get('/parent-child/parents', [ParentChildController::class, 'getParents']);// mendapatkan data ortu yang sudah konfirm
Route::middleware('auth:sanctum')->get('/parent-child/kelas-anak', [ParentChildController::class, 'getKelasAnak']);


// Route::get('/search-student', [UserController::class, 'searchStudent']);
Route::get('/view-child/{parent_id}', [ParentChildController::class, 'viewChild']);

