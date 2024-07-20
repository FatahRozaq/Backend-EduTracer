<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\KelasUser;
use App\Models\Kelas;
use App\Models\KelasMataPelajaran;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function getKelas()
    {
        $kelas = Kelas::all();

        return response()->json($kelas);
    }
    public function getAvailableSchedules($classId)
    {
        try {
            $now = request('current_time');
            $dayName = $now->dayName;

            // $schedules = Jadwal::where('id_kelas', $classId)
            //             ->where('hari', 'LIKE', '%' . $dayName . '%')
            //             // ->where('jam_mulai', '<=', $now->format('H:i:s'))
            //             // ->where('jam_akhir', '>=', $now->format('H:i:s'))
            //             ->get();

            $schedules = Jadwal::with('kelas')
                ->where('id_kelas', $classId)
                ->where('hari', 'LIKE', '%' . $dayName . '%')
                ->get();

                $namaKelas = $schedules->map(function ($schedule) {
                    return $schedule->kelas->nama_kelas;
                });

            dd($namaKelas);

            return response()->json($schedules);

            if ($schedules->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No available schedules for this time',
                    'data' => []
                ], 403);
            }

            $schedules = $schedules->map(function ($schedule) use ($dayName) {
                $schedule->hari = $dayName;
                // $schedule->id_guru = $schedule->kelas->nama_kelas;
                $schedule->id_kelas = $schedule->kelas->nama_kelas;
                return $schedule;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Available schedules retrieved successfully',
                'data' => $schedules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getStudentAvailabeAttendances($classId)
    {
        $studentInClass = KelasUser::with(['user', 'kelas'])
                                ->where('id_kelas', $classId)
                                ->whereHas('user', function($query) {
                                    $query->where('roles', 'Siswa');
                                })
                                ->get();

        return response()->json($studentInClass);

    }

    public function getAttendance(Request $request, $classId)
    {
        try {
            $date = $request['tanggal'];
            $idSubjectClass = $request['idKelasMataPelajaran'];
            $formattedDate = Carbon::parse($date)->format('Y-m-d');

            $presence = Absensi::where('id_kelas_mata_pelajaran', $idSubjectClass)
                            ->where('tanggal', $formattedDate)
                            ->get();

            if(!$presence)
            {
                return response()->json(['message' => 'gagal mengambil data'], 403);
            }

            return response()->json($presence);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }


    // public function getAttendance(Request $request, $classId)
    // {
    //     try {
    //         $now = request('current_time');
    //         $dayName = $now->dayName;

    //         $schedule = Jadwal::where('id_kelas', $classId)
    //                     ->where('hari', 'LIKE', '%' . $dayName . '%')
    //                     ->where('jam_mulai', '<=', $now->format('H:i:s'))
    //                     ->where('jam_akhir', '>=', $now->format('H:i:s'))
    //                     ->first();

    //         if (!$schedule) {
    //             return response()->json(['error' => 'No attendance records for this time'], 403);
    //         }

    //         $attendanceRecords = Absensi::where('id_jadwal', $schedule->id)
    //                             ->where('date', $now->format('Y-m-d'))
    //                             ->get();

    //         return response()->json($attendanceRecords);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'An error occurred: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function getAttendanceStatus(Request $request)
    {
        $date = $request['tanggal'];
        $formattedDate = Carbon::parse($date)->format('Y-m-d');
        $idSubjectClass = $request['idKelasMataPelajaran'];
        $presence = Absensi::where('id_kelas_mata_pelajaran', $idSubjectClass)
                        ->where('tanggal', $formattedDate)
                        ->first();

        if(!$presence)
        {
            return response()->json(['status' => 'Not yet']);
        }

        return response()->json(['status' => 'Done'], 403);
    }

    public function getAttendanceStudentStatus(Request $request)
    {
        try {
            $now = request('current_time');
            $dayName = $now->dayName;

            // dd($tanggal);

            $presence = Absensi::where('id_user', $request['userId'])
                        ->where('id_kelas_mata_pelajaran', $request['idKelasMataPelajaran'])
                        ->where('tanggal', $now->format('Y-m-d'))
                        ->first();

            // dd($presence);

            if(!$presence)
            {
                return response()->json(['error' => 'No attendance records for this time'], 403);
            }

            return response()->json(['data' => $presence], 403);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    // public function markAttendance(Request $request, $classId)
    // {
    //     try {
    //         $userId = $request['userId'];
    //         $idSubject = $request['idMataPelajaran'];
    //         $idSubjectClass = $request['idKelasMataPelajaran'];


    //         $now = request('current_time');
    //         $dayName = $now->dayName;

    //         $schedule = Jadwal::where('id_kelas', $classId)
    //                     ->where('hari', 'LIKE', '%' . $dayName . '%')
    //                     ->where('id_mata_pelajaran', $idSubject)
    //                     ->where('jam_mulai', '<=', $now->format('H:i:s'))
    //                     ->where('jam_akhir', '>=', $now->format('H:i:s'))
    //                     ->first();

    //         if (!$schedule) {
    //             return response()->json(['error' => 'Attendance is not allowed at this time'], 403);
    //         }

    //         $scheduleId = $schedule->id_jadwal;

    //         $attendance = Absensi::create([
    //             'id_kelas_mata_pelajaran' => $idSubjectClass,
    //             'id_jadwal' => $scheduleId,
    //             'id_user' => $userId,
    //             'status_kehadiran' => $request->status_kehadiran,
    //             'tanggal' => $now->format('Y-m-d'),
    //         ]);

    //         return response()->json($attendance);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'An error occurred: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function markAttendance(Request $request, $classId)
    {
        try {
            $attendancesData = $request['attendances']; // Mengambil array yang berisi data kehadiran untuk setiap pengguna
            $idSubject = $request['idMataPelajaran'];
            $idSubjectClass = $request['idKelasMataPelajaran'];
            $now = Carbon::parse($request['current_time']); // Pastikan menggunakan Carbon untuk parsing tanggal
            $dayName = $now->dayName;

            $schedule = Jadwal::where('id_kelas', $classId)
                // ->where('hari', 'LIKE', '%' . $dayName . '%')
                ->where('id_mata_pelajaran', $idSubject)
                // ->where('jam_mulai', '<=', $now->format('H:i:s'))
                // ->where('jam_akhir', '>=', $now->format('H:i:s'))
                ->first();

            if (!$schedule) {
                return response()->json(['error' => 'Attendance is not allowed at this time'], 403);
            }

            $scheduleId = $schedule->id_jadwal;
            $attendances = [];

            foreach ($attendancesData as $attendanceData) {
                $userId = $attendanceData['userId'];
                $statusKehadiran = $attendanceData['status_kehadiran'];

                $attendance = Absensi::create([
                    'id_kelas_mata_pelajaran' => $idSubjectClass,
                    'id_jadwal' => $scheduleId,
                    'id_user' => $userId,
                    'status_kehadiran' => $statusKehadiran,
                    'tanggal' => $now->format('Y-m-d'),
                ]);

                $attendances[] = $attendance;
            }

            return response()->json($attendances);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateAttendance(Request $request, $classId)
    {
        try {
            $attendancesData = $request['attendances']; // Mengambil array yang berisi data kehadiran untuk setiap pengguna
            $idSubject = $request['idMataPelajaran'];
            $idSubjectClass = $request['idKelasMataPelajaran'];
            $now = Carbon::parse($request['current_time']); // Pastikan menggunakan Carbon untuk parsing tanggal
            $dayName = $now->dayName;

            $schedule = Jadwal::where('id_kelas', $classId)
                ->where('id_mata_pelajaran', $idSubject)
                ->first();

            if (!$schedule) {
                return response()->json(['error' => 'Attendance is not allowed at this time'], 403);
            }

            $scheduleId = $schedule->id_jadwal;
            $attendances = [];

            foreach ($attendancesData as $attendanceData) {
                $userId = $attendanceData['userId'];
                $statusKehadiran = $attendanceData['status_kehadiran'];

                // Cari entri absensi yang ada
                $attendance = Absensi::where('id_kelas_mata_pelajaran', $idSubjectClass)
                    ->where('id_jadwal', $scheduleId)
                    ->where('id_user', $userId)
                    ->whereDate('tanggal', $now->format('Y-m-d'))
                    ->first();

                if ($attendance) {
                    // Update entri absensi yang ada
                    $attendance->status_kehadiran = $statusKehadiran;
                    $attendance->save();
                } else {
                    // Buat entri absensi baru jika tidak ada
                    $attendance = Absensi::create([
                        'id_kelas_mata_pelajaran' => $idSubjectClass,
                        'id_jadwal' => $scheduleId,
                        'id_user' => $userId,
                        'status_kehadiran' => $statusKehadiran,
                        'tanggal' => $now->format('Y-m-d'),
                    ]);
                }

                $attendances[] = $attendance;
            }

            return response()->json($attendances);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    // public function getFilterAbsensi(Request $request)
    // {
    //     $userId = $request['id_user'];
    //     $kelasId = $request['id_kelas'];

    //     // $mapel = MataPelajaran::where('id_user', $userId)->get();

    //     // $absensi = KelasUser::where();

    //     $guru = MataPelajaran::where('id_user')
    //     $jadwal = Jadwal::where()

    // }
    




}
