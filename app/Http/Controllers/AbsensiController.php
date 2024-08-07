<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Kelas;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\KelasUser;
use Illuminate\Http\Request;
use App\Models\MataPelajaran;
use App\Models\JadwalPengajar;
use App\Models\KelasMataPelajaran;
use App\Models\ParentChild;

class AbsensiController extends Controller
{
    public function __construct()
    {
        Carbon::setLocale('id');
    }

    private function getCurrentTime()
    {
        return Carbon::now('Asia/Jakarta');
    }

    public function getKelas()
    {
        $kelas = Kelas::all();
        return response()->json($kelas);
    }

    public function getAvailableSchedules($classId)
    {
        try {
            $now = $this->getCurrentTime();
            $dayName = $now->dayName;

            $schedules = Jadwal::with('kelas')
                ->where('id_kelas', $classId)
                ->where('hari', 'LIKE', '%' . $dayName . '%')
                ->get();

            $namaKelas = $schedules->map(function ($schedule) {
                return $schedule->kelas->nama_kelas;
            });

            if ($schedules->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No available schedules for this time',
                    'data' => []
                ], 403);
            }

            $schedules = $schedules->map(function ($schedule) use ($dayName) {
                $schedule->hari = $dayName;
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

    public function getAttendance(Request $request)
    {
        try {
            $date = $request['tanggal'];
            $idSubjectClass = $request['idKelasMataPelajaran'];
            $formattedDate = Carbon::parse($date)->format('Y-m-d');

            $presence = Absensi::with('user')
                            ->where('id_kelas_mata_pelajaran', $idSubjectClass)
                            ->where('tanggal', $formattedDate)
                            ->get();

            if(!$presence) {
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

    public function getAttendanceStatus(Request $request)
    {
        $date = $request['tanggal'];
        $formattedDate = Carbon::parse($date)->format('Y-m-d');
        $idSubjectClass = $request['idKelasMataPelajaran'];
        $presence = Absensi::where('id_kelas_mata_pelajaran', $idSubjectClass)
                        ->where('tanggal', $formattedDate)
                        ->first();

        if(!$presence) {
            return response()->json(['status' => 'Not yet']);
        }

        return response()->json(['status' => 'Done'], 403);
    }

    public function getAttendanceStudentStatus(Request $request)
    {
        try {
            $now = $this->getCurrentTime();
            $dayName = $now->dayName;

            $presence = Absensi::where('id_user', $request['userId'])
                        ->where('id_kelas_mata_pelajaran', $request['idKelasMataPelajaran'])
                        ->where('tanggal', $now->format('Y-m-d'))
                        ->first();

            if(!$presence) {
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

    public function markAttendance(Request $request, $classId)
    {
        try {
            $attendancesData = $request['attendances'];
            $idSubject = $request['idMataPelajaran'];
            $idSubjectClass = $request['idKelasMataPelajaran'];
            $now = Carbon::parse($request['current_time']);
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
            $attendancesData = $request['attendances'];
            $idSubject = $request['idMataPelajaran'];
            $idSubjectClass = $request['idKelasMataPelajaran'];
            $now = Carbon::parse($request['current_time']);
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

                $attendance = Absensi::where('id_kelas_mata_pelajaran', $idSubjectClass)
                    ->where('id_jadwal', $scheduleId)
                    ->where('id_user', $userId)
                    ->whereDate('tanggal', $now->format('Y-m-d'))
                    ->first();

                if ($attendance) {
                    $attendance->status_kehadiran = $statusKehadiran;
                    $attendance->save();
                } else {
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
    //     try {
    //         $userId = $request['id_user'];
    //         $now = Carbon::parse($request->input('current_time'));
    //         $dayName = $now->dayName;

    //         $pengajars = JadwalPengajar::where('id_user', $userId)->get();
    //         $jadwals = collect();
    //         foreach ($pengajars as $pengajar) {
    //             $pengajarJadwals = Jadwal::with(['kelas', 'mataPelajaran'])
    //                                     ->where('id_jadwal', $pengajar->id_jadwal)
    //                                     ->get()
    //                                     ->filter(function($jadwal) use ($dayName) {
    //                                         return in_array($dayName, $jadwal->hari);
    //                                     });
    //             $jadwals = $jadwals->merge($pengajarJadwals);
    //         }

    //         return response()->json($jadwals);
    //     } catch (Exception $e) {
    //         return response()->json(['error' => 'Terjadi kesalahan server. Silakan coba kembali' . $e->getMessage()], 500);
    //     }
    // }

    public function getFilterAbsensi(Request $request)
{
    try {
        $userId = (int)$request['id_user'];
        $now = Carbon::parse($request->input('current_time'));
        $dayName = $now->dayName;

        $pengajars = JadwalPengajar::where('id_user', $userId)->get();
        $jadwals = collect();

        foreach ($pengajars as $pengajar) {
            $pengajarJadwals = Jadwal::with(['kelas', 'mataPelajaran'])
                ->where('id_jadwal', (int)$pengajar->id_jadwal)
                ->get()
                ->filter(function ($jadwal) use ($dayName) {
                    $hariArray = explode(',', $jadwal->hari);
                    return in_array($dayName, $hariArray);
                });

            $jadwals = $jadwals->merge($pengajarJadwals);
        }

        return response()->json($jadwals);
    } catch (Exception $e) {
        return response()->json(['error' => 'Terjadi kesalahan server. Silakan coba kembali. Error: ' . $e->getMessage()], 500);
    }
}
    public function getAbsenStatus(Request $request)
    {
        try {
            $idJadwal = $request['id_jadwal'];
            $now = Carbon::parse($request->input('current_time'));

            $absen = Absensi::where('id_jadwal', $idJadwal)
                        ->whereDate('tanggal', $now->format('Y-m-d'))
                        ->get();

            if ($absen->isNotEmpty()) {
                return response()->json(['statusAbsen' => 'Done']);
            }
            return response()->json(['statusAbsen' => 'Not Yet']);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function getKelasMapel(Request $request)
    {
        try {
            $idKelas = $request['id_kelas'];
            $idMapel = $request['id_mata_pelajaran'];
            $data = KelasMataPelajaran::where('id_kelas', $idKelas)
                        ->where('id_mata_pelajaran', $idMapel)
                        ->get();

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data kelas mata pelajaran.', 'message' => $e->getMessage()], 500);
        }
    }

    public function getAbsenSiswa(Request $request)
    {
        try {
            $idUser = $request['id_user'];

            $partner = ParentChild::where('parent_id', $idUser)->first();

            if($partner)
            {
                $idUser = $partner->child_id;
            }
            
            $now = $this->getCurrentTime();
            // $idUser = $request['id_user'];
            $idClass = KelasUser::where('id_user', $idUser)->value('id_kelas');

            $absen = Jadwal::with('mataPelajaran')
                        ->where('id_kelas', $idClass)
                        ->where('hari', $now->dayName)
                        ->orderBy('jam_mulai', 'asc')
                        ->get();

            $idJadwalList = $absen->pluck('id_jadwal');

            $pengajar = JadwalPengajar::with('user')
                            ->whereIn('id_jadwal', $idJadwalList)
                            ->get()
                            ->groupBy('id_jadwal');

            $absen = $absen->map(function($item) use ($pengajar) {
                $item->pengajar = $pengajar->get($item->id_jadwal) ?? [];
                return $item;
            });

            return response()->json($absen);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data absen siswa.', 'message' => $e->getMessage()], 500);
        }
    }

    public function getAbsenSiswaStatus(Request $request)
    {
        try {
            $idUser = $request['id_user'];

            $partner = ParentChild::where('parent_id', $idUser)->first();

            if($partner)
            {
                $idUser = $partner->child_id;
            }

            $now = $this->getCurrentTime();
            // $idUser = $request['id_user'];
            $idJadwal = $request['id_jadwal'];
            
            $status = Absensi::where('id_user', $idUser)
                        ->where('id_jadwal', $idJadwal)
                        ->where('tanggal', $now->format('Y-m-d'))
                        ->get();

            if ($status->isEmpty()) {
                return response()->json(['message' => 'Belum absen']);
            }

            return response()->json([
                'message' => 'Ada',
                'status' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil status absen siswa.', 'message' => $e->getMessage()], 500);
        }
    }
}