<?php

use Illuminate\Http\Request;
use App\Http\Middleware\SetTimezone;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuratIzinController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\AbsensiController;

use App\Http\Controllers\ParentChildController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MataPelajaranController;
use App\Http\Controllers\TugasKelasMataPelajaranController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\RapotController;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


//user
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/auth/user', [AuthController::class, 'getUser']);
Route::middleware('auth:sanctum')->put('/auth/user', [AuthController::class, 'updateUser']);
Route::middleware('auth:sanctum')->post('/auth/user/change-password', [AuthController::class, 'changePassword']);
Route::middleware('auth:sanctum')->get('/users/guru', [UserController::class, 'getAllGuru']);
Route::middleware('auth:sanctum')->get('/siswa-by-kelas/{id_kelas}', [UserController::class, 'getSiswaByKelasId']);

//tugas
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
    Route::get('/absensi/guru/filter', [AbsensiController::class, 'getFilterAbsensi']);
});

// Route::get('/classes/{classId}/attendance', [AbsensiController::class, 'getClassAttendance']);
// Route::get('/students/{studentId}/attendance', [AbsensiController::class, 'getStudentAttendance']);



//Surat Izin
Route::middleware('auth:sanctum')->put('/orangtua/suratizin/{id}', [SuratIzinController::class, 'update']);
Route::middleware('auth:sanctum')->get('/orangtua/suratizin', [SuratIzinController::class, 'index']);
Route::middleware('auth:sanctum')->get('/orangtua/suratizin/{id}', [SuratIzinController::class, 'show']);
Route::middleware('auth:sanctum')->get('/guru/suratizin/{id}', [SuratIzinController::class, 'show']);
Route::middleware('auth:sanctum')->post('/orangtua/suratizin', [SuratIzinController::class, 'store']);
Route::middleware('auth:sanctum')->delete('/orangtua/suratizin/{id}', [SuratIzinController::class, 'destroy']);
Route::middleware('auth:sanctum')->get('/guru/suratizin', [SuratIzinController::class, 'index']);




//Kelas
Route::middleware('auth:sanctum')->post('/kelas/enroll', [KelasController::class, 'enroll']);
Route::post('/kelas/tambah', [KelasController::class, 'store']);
Route::middleware('auth:sanctum')->delete('/kelas/{id_kelas}', [KelasController::class, 'destroy']);
Route::middleware('auth:sanctum')->put('/kelas/{id_kelas}', [KelasController::class, 'update']);

Route::middleware('auth:sanctum')->get('/kelas/getkelasuser', [KelasController::class, 'getKelasByUserId']);
Route::middleware('auth:sanctum')->get('/kelas/pendingKelas', [KelasController::class, 'getKelasPending']);
Route::get('/kelas/user', [KelasController::class, 'getKelasByUserId']); // gabungan

Route::middleware('auth:sanctum')->post('/kelas/{id_kelas}/addMataPelajaran', [KelasController::class, 'addMataPelajaran']);
Route::middleware('auth:sanctum')->get('/kelas/{id_kelas}/mata-pelajaran', [KelasController::class, 'getMataPelajaran']);
Route::middleware('auth:sanctum')->delete('/kelas-user/{id_kelas}', [KelasController::class, 'destroyKelasUser']);
Route::middleware('auth:sanctum')->post('/kelas/search', [KelasController::class, 'searchKelas']);
Route::middleware('auth:sanctum')->get('/kelas/{id_kelas}/guru', [KelasController::class, 'getGuruInClass']);
Route::middleware('auth:sanctum')->get('/kelas-by-wakel', [KelasController::class, 'getKelasByLoggedInWakel']);

Route::middleware('auth:sanctum')->get('/kelas/{id_kelas}/siswa', [KelasController::class, 'getSiswaByClassId']);
Route::middleware('auth:sanctum')->get('/kelas/{id_kelas}/users', [KelasController::class, 'getUserByClassId']);
// Route::get('/kelas/{id_kelas}/users', [KelasController::class, 'getUsersByClassId']);// gabungan

Route::middleware('auth:sanctum')->get('/kelas/{id_kelas}/pending-students', [KelasController::class, 'getPendingStudentsByClassId']);
Route::middleware('auth:sanctum')->post('/kelas/{id_kelas}/confirm-student', [KelasController::class, 'confirmStudent']);



//Mata pelajaran
Route::middleware('auth:sanctum')->get('/mapel-guru', [MataPelajaranController::class, 'getMataPelajaranByLoggedInUser']);

Route::middleware('auth:sanctum')->post('/mapel/{id_kelas}/create', [MataPelajaranController::class, 'storeMataPelajaran']);
Route::middleware('auth:sanctum')->post('/mapel/create', [MataPelajaranController::class, 'storeMataPelajaranonly']);
Route::middleware('auth:sanctum')->delete('/kelas/{id_kelas}/mata-pelajaran/{id_mata_pelajaran}', [MataPelajaranController::class, 'destroyKelasMataPelajaran']);
Route::middleware('auth:sanctum')->get('/mata-pelajaran/search', [MataPelajaranController::class, 'searchMataPelajaran']);
Route::middleware('auth:sanctum')->put('/mata-pelajaran/update/{id_mata_pelajaran}', [MataPelajaranController::class, 'updateMataPelajaran']);
Route::middleware('auth:sanctum')->delete('/mata-pelajaran/destroy/{id_mata_pelajaran}', [MataPelajaranController::class, 'destroyMataPelajaran']);

Route::middleware('auth:sanctum')->post('/kelas/{id_kelas}/add-mata-pelajaran-by-kode', [KelasController::class, 'addMataPelajaranByKode']);
// Route::middleware('auth:sanctum')->get('/tugas-kelas-mata-pelajaran', [TugasKelasMataPelajaranController::class, 'getTugasByUser']);
// Route::middleware('auth:sanctum')->post('/tugas-kelas-mata-pelajaran/search', [TugasKelasMataPelajaranController::class, 'searchTugas']);
// Route::middleware('auth:sanctum')->put('/tugas-kelas-mata-pelajaran/update/{id}', [TugasKelasMataPelajaranController::class, 'updateTugasKelasMataPelajaran']);



//rapot ujian dan nilai
Route::post('/create-rapot', [RapotController::class, 'createRapot']);
Route::post('/create-rapotline', [RapotController::class, 'createRapotLine']);
Route::middleware('auth:sanctum')->get('/rapot/{id}/lines', [RapotController::class, 'getRapotLinesByRapotId']);
Route::post('/rapot/get', [RapotController::class, 'getRapotId']);
Route::post('/rapot/check', [RapotController::class, 'checkRapot']);
Route::middleware('auth:sanctum')->put('/rapot-line/{id_rapot_line}', [RapotController::class, 'updateRapotLine']);
Route::middleware('auth:sanctum')->get('/rapot/siswa', [RapotController::class, 'getRapotsByLoggedInStudent']);





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

