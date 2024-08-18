<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Tugas;
use App\Models\KelasUser;
use Illuminate\Http\Request;
use App\Models\PengajarMapel;
use App\Models\KelasMataPelajaran;
use App\Models\TugasKelasMataPelajaran;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TugasController extends Controller
{
    public function __construct()
    {
        Carbon::setLocale('id');
    }

    private function getCurrentTime()
    {
        return Carbon::now('Asia/Jakarta');
    }

    public function index()
    {
        try {
            $tugas = Tugas::orderBy('id_tugas', 'ASC')->get();
            return response()->json([
                'success' => true,
                'data' => $tugas,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $tugas = Tugas::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $tugas,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }

//     public function store(Request $request)
// {
//     try {
//         // Ambil id_kelas dan id_mata_pelajaran dari request
//         $idKelas = $request['id_kelas'];
//         $idMapel = $request['id_mata_pelajaran'];

//         // Cari id_kelas_mata_pelajaran dari KelasMataPelajaran
//         $kelasMapel = KelasMataPelajaran::where('id_kelas', $idKelas)
//                                         ->where('id_mata_pelajaran', $idMapel)
//                                         ->first();

//         // Periksa apakah kelasMapel ditemukan
//         if (!$kelasMapel) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Kelas Mata Pelajaran tidak ditemukan.',
//             ], 404);
//         }

//         // Validasi data request
//         $validatedData = $request->validate([
//             'nama_tugas' => 'required',
//             'deskripsi' => 'required',
//             'status' => 'required',
//             'tenggat_tugas' => 'required|date',
//             'file' => 'nullable|file|max:4096'
//         ]);

//         // Tambahkan id_kelas_mata_pelajaran ke dalam data yang divalidasi
//         $validatedData['id_kelas_mata_pelajaran'] = $kelasMapel->id_kelas_mata_pelajaran;

//         // Buat entri tugas baru
//         $tugas = Tugas::create($validatedData);

//         // Kembalikan respons sukses
//         return response()->json([
//             'success' => true,
//             'data' => $tugas,
//         ], 201);

//     } catch (\Illuminate\Validation\ValidationException $e) {
//         // Kembalikan respons kesalahan validasi
//         return response()->json([
//             'success' => false,
//             'message' => $e->errors(),
//         ], 422);
//     } catch (\Exception $e) {
//         // Kembalikan respons kesalahan umum
//         return response()->json([
//             'success' => false,
//             'message' => 'An error occurred',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }

public function store(Request $request)
{
    try {
        // Ambil id_kelas dan id_mata_pelajaran dari request
        $idKelas = $request['id_kelas'];
        $idMapel = $request['id_mata_pelajaran'];

        // Cari id_kelas_mata_pelajaran dari KelasMataPelajaran
        $kelasMapel = KelasMataPelajaran::where('id_kelas', $idKelas)
                                        ->where('id_mata_pelajaran', $idMapel)
                                        ->first();

        // Periksa apakah kelasMapel ditemukan
        if (!$kelasMapel) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas Mata Pelajaran tidak ditemukan.',
            ], 404);
        }

        // Validasi data request
        $validatedData = $request->validate([
            'nama_tugas' => 'required',
            'deskripsi' => 'required',
            'tenggat_tugas' => 'required|date',
            'file' => 'nullable|file|max:4096|mimes:jpg,jpeg,png,pdf,doc,docx'
        ]);

        // Get the current date and time
        $now = $this->getCurrentTime();

        // Parse tenggat_tugas to a Carbon instance
        $tenggatTugas = Carbon::parse($validatedData['tenggat_tugas'], 'Asia/Jakarta');

        // Get start of the week for both the current time and deadline
        $startOfWeekNow = $now->copy()->startOfWeek();
        $endOfWeekNow = $now->copy()->endOfWeek();
        $startOfNextWeek = $startOfWeekNow->copy()->addWeek();
        $endOfNextWeek = $endOfWeekNow->copy()->addWeek();
        $endOfMonthNow = $now->copy()->endOfMonth();

        // Determine the status based on the deadline
        if ($tenggatTugas->isPast()) {
            $status = 'Lewat'; // Status baru untuk tugas yang sudah lewat tenggat
        } elseif($tenggatTugas->isToday()) {
            $status = 'Hari ini';
        } elseif ($tenggatTugas->isTomorrow()) {
            $status = 'Besok';
        } elseif ($tenggatTugas->between($startOfWeekNow, $endOfWeekNow)) {
            $status = 'Minggu ini';
        } elseif ($tenggatTugas->between($startOfNextWeek, $endOfNextWeek)) {
            $status = 'Minggu depan';
        } elseif ($tenggatTugas->month == $now->month) {
            $status = 'Bulan ini';
        }  else {
            $status = 'Diluar bulan ini';
        }

        // Tambahkan status ke dalam data yang divalidasi
        $validatedData['status'] = $status;

        // Tambahkan id_kelas_mata_pelajaran ke dalam data yang divalidasi
        $validatedData['id_kelas_mata_pelajaran'] = $kelasMapel->id_kelas_mata_pelajaran;

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
        
            // Ensure a valid uploaded file
            if ($file->isValid()) {
                // Generate a unique filename using timestamp and original name
                $filename = time() . '_' . $file->getClientOriginalName();
        
                // Store the file in the 'uploads/tugas' directory within the 'public' disk
                $path = $file->storeAs('uploads/tugas', $filename, 'public');
                
                // Add the file path to validated data
                $validatedData['file_path'] = $path;
                // return response()->json($validatedData['file_path']);
            }
        }

        // Buat entri tugas baru
        $tugas = Tugas::create($validatedData);

        // Kembalikan respons sukses
        return response()->json([
            'success' => true,
            'data' => $tugas,
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Kembalikan respons kesalahan validasi
        return response()->json([
            'success' => false,
            'message' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        // Kembalikan respons kesalahan umum
        return response()->json([
            'success' => false,
            'message' => 'An error occurred',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function update(Request $request, $id)
    {
        try {
            $tugas = Tugas::findOrFail($id);

            $validatedData = $request->validate([
                'nama_tugas' => 'required',
                'deskripsi' => 'required',
                'tenggat_tugas' => 'required|date',
                'status' => 'required',
                'id_kelas_mata_pelajaran' => 'required',
                'file_tugas' => 'nullable|file|max:4096'
            ]);

            $tugas->update($validatedData);
            return response()->json([
                'success' => true,
                'data' => $tugas,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas not found',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $tugas = Tugas::findOrFail($id);
            $tugas->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }



    // Kelompok Tugas

    public function getTugasDetail($id_tugas)
    {
        try {
            $tugas = Tugas::findOrFail($id_tugas);

            return response()->json([
                'success' => true,
                'data' => $tugas,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }
    }


    public function storeKelompokTugas(Request $request)
    {
        try {
            // Validate the single input fields
            $validatedData = $request->validate([
                'nama_tugas' => 'required',
                'deskripsi' => 'required',
                'status' => 'required',
            ]);

            // Validate the multiple input fields
            $multipleFields = $request->validate([
                'tenggat_tugas' => 'required|array',
                'tenggat_tugas.*' => 'required|date',
                'id_kelas_mata_pelajaran' => 'required|array',
                'id_kelas_mata_pelajaran.*' => 'required'
            ]);

            // Arrays to store multiple entries
            $tenggatTugasArray = $multipleFields['tenggat_tugas'];
            $idKelasMataPelajaranArray = $multipleFields['id_kelas_mata_pelajaran'];

            $tugasEntries = [];

            // Loop through multiple input fields and create entries
            foreach ($tenggatTugasArray as $index => $tenggatTugas) {
                $tugasEntries[] = [
                    'nama_tugas' => $validatedData['nama_tugas'],
                    'deskripsi' => $validatedData['deskripsi'],
                    'status' => $validatedData['status'],
                    'tenggat_tugas' => $tenggatTugas,
                    'id_kelas_mata_pelajaran' => $idKelasMataPelajaranArray[$index],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Insert multiple entries into the database
            Tugas::insert($tugasEntries);

            return response()->json([
                'success' => true,
                'data' => $tugasEntries,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }

    public function storeAdditionalData(Request $request, $id_tugas)
    {
        try {
            // Retrieve the task details by id
            $tugas = Tugas::findOrFail($id_tugas);

            // Validate the multiple input fields
            $multipleFields = $request->validate([
                'tenggat_tugas' => 'required|array',
                'tenggat_tugas.*' => 'required|date',
                'id_kelas_mata_pelajaran' => 'required|array',
                'id_kelas_mata_pelajaran.*' => 'required'
            ]);

            // Arrays to store multiple entries
            $tenggatTugasArray = $multipleFields['tenggat_tugas'];
            $idKelasMataPelajaranArray = $multipleFields['id_kelas_mata_pelajaran'];

            $tugasEntries = [];

            // Loop through multiple input fields and create entries
            foreach ($tenggatTugasArray as $index => $tenggatTugas) {
                $tugasEntries[] = [
                    'nama_tugas' => $tugas->nama_tugas,
                    'deskripsi' => $tugas->deskripsi,
                    'status' => $tugas->status,
                    'tenggat_tugas' => $tenggatTugas,
                    'id_kelas_mata_pelajaran' => $idKelasMataPelajaranArray[$index],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Insert multiple entries into the database
            Tugas::insert($tugasEntries);

            return response()->json([
                'success' => true,
                'data' => $tugasEntries,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }

    public function editTugasByNama(Request $request, $nama_tugas)
    {
        try {
            // Validate the input fields
            $validatedData = $request->validate([
                'deskripsi' => 'sometimes|required',
                'status' => 'sometimes|required',
                'tenggat_tugas' => 'required|array',
                'tenggat_tugas.*' => 'required|date',
                'id_kelas_mata_pelajaran' => 'required|array',
                'id_kelas_mata_pelajaran.*' => 'required'
            ]);

            // Retrieve the tasks by nama_tugas
            $tasks = Tugas::where('nama_tugas', $nama_tugas)->get();

            if ($tasks->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found',
                ], 404);
            }

            // Update each task entry based on id_kelas_mata_pelajaran
            foreach ($tasks as $task) {
                $index = array_search($task->id_kelas_mata_pelajaran, $validatedData['id_kelas_mata_pelajaran']);
                if ($index !== false) {
                    // Update the task entry with new data
                    $task->update([
                        'deskripsi' => $request->input('deskripsi', $task->deskripsi),
                        'status' => $request->input('status', $task->status),
                        'tenggat_tugas' => $validatedData['tenggat_tugas'][$index],
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $tasks,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }

    public function deleteTugasByNama(Request $request, $nama_tugas)
    {
        try {
            // Validate the input fields
            $validatedData = $request->validate([
                'id_kelas_mata_pelajaran' => 'required|array',
                'id_kelas_mata_pelajaran.*' => 'required'
            ]);

            // Retrieve the tasks by nama_tugas and id_kelas_mata_pelajaran
            $tasks = Tugas::where('nama_tugas', $nama_tugas)
                        ->whereIn('id_kelas_mata_pelajaran', $validatedData['id_kelas_mata_pelajaran'])
                        ->get();

            if ($tasks->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tasks found for the given criteria',
                ], 404);
            }

            // Delete the tasks
            foreach ($tasks as $task) {
                $task->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Tasks deleted successfully',
                'data' => $tasks,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }

    public function getMapelGuru(Request $request)
    {
        try {
            $userId = $request->input('userId');
            
            // Ambil data mapel yang diajar oleh guru
            $mapelGuru = PengajarMapel::with('mataPelajaran')
                            ->where('id_user', $userId)
                            ->get();

            // Return response dengan data mapel guru dan kelas mata pelajaran
            return response()->json([
                'success' => true,
                'data' => [
                    'mapelGuru' => $mapelGuru
                ]
            ], 200);

        } catch (\Exception $e) {
            // Handle errors
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getKelasGuru(Request $request)
    {
        try {
            $userId = $request->input('userId');
            
            $kelas = KelasUser::with('kelas')
                            ->where('id_user', $userId)
                            ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'kelas' => $kelas
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


//     public function getMapelGuru(Request $request)
// {
//     try {
//         $userId = $request->input('userId');
        
//         // Ambil data mapel yang diajar oleh guru
//         $mapelGuru = PengajarMapel::where('id_user', $userId)->get();

//         // Menyimpan hasil pencarian kelas mata pelajaran
//         $kelasMataPelajaranList = [];

//         foreach ($mapelGuru as $mapel) {
//             // Query untuk mendapatkan semua KelasMataPelajaran berdasarkan id_mata_pelajaran dan id_kelas
//             $kelasMataPelajaran = KelasMataPelajaran::where('id_mata_pelajaran', $mapel->id_mata_pelajaran)
//                                                      ->where('id_kelas', $mapel->id_kelas)
//                                                      ->get();
            
//             // Gabungkan hasil ke dalam list
//             if (!$kelasMataPelajaran->isEmpty()) {
//                 $kelasMataPelajaranList = array_merge($kelasMataPelajaranList, $kelasMataPelajaran->toArray());
//             }
//         }

//         // Return response dengan data mapel guru dan kelas mata pelajaran
//         return response()->json([
//             'success' => true,
//             'data' => [
//                 'mapelGuru' => $mapelGuru,
//                 'kelasMataPelajaran' => $kelasMataPelajaranList
//             ]
//         ], 200);

//     } catch (\Exception $e) {
//         // Handle errors
//         return response()->json([
//             'success' => false,
//             'message' => 'Terjadi kesalahan saat mengambil data.',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }





    // public function store(Request $request)
    // {
    //     $idKelas = $request['id_kelas'];
    //     // $idMapel = $request['id_mata_pelajaran'];
    //     try {
    //         $validatedData = $request->validate([
    //             'nama_tugas' => 'required',
    //             'deskripsi' => 'required',
    //             'status' => 'required',
    //             'tenggat_tugas' => 'required|date',
    //             // 'id_kelas_mata_pelajaran' => 'required'
    //         ]);

    //         $tugas = Tugas::create($validatedData);
    //         return response()->json([
    //             'success' => true,
    //             'data' => $tugas,
    //         ], 201);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->errors(),
    //         ], 422);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred',
    //         ], 500);
    //     }
    // }

    

    public function getTugasByKelasMataPelajaran($id_kelas_mata_pelajaran)
    {
        try {
            $tugas = Tugas::where('id_kelas_mata_pelajaran', $id_kelas_mata_pelajaran)->get();
            return response()->json([
                'success' => true,
                'data' => $tugas,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }

    public function storeBaru(Request $request)
{
    try {
        // Ambil id_kelas dan id_mata_pelajaran dari request
        $idKelas = (int) $request->input('id_kelas');
        $idMapel = (int) $request->input('id_mata_pelajaran');

        // Cari id_kelas_mata_pelajaran dari KelasMataPelajaran
        $kelasMapel = KelasMataPelajaran::where('id_kelas', $idKelas)
                                        ->where('id_mata_pelajaran', $idMapel)
                                        ->first();

        // Periksa apakah kelasMapel ditemukan
        if (!$kelasMapel) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas Mata Pelajaran tidak ditemukan.',
            ], 404);
        }

        // Validasi data request
        $validatedData = $request->validate([
            'nama_tugas' => 'required',
            'deskripsi' => 'required',
            'tenggat_tugas' => 'required|date',
            'file' => 'nullable|file|max:4096|mimes:jpg,jpeg,png,pdf,doc,docx'
        ]);

        // Get the current date and time
        $now = $this->getCurrentTime();

        // Parse tenggat_tugas to a Carbon instance
        $tenggatTugas = Carbon::parse($validatedData['tenggat_tugas'], 'Asia/Jakarta');

        // Get start of the week for both the current time and deadline
        $startOfWeekNow = $now->copy()->startOfWeek();
        $endOfWeekNow = $now->copy()->endOfWeek();
        $startOfNextWeek = $startOfWeekNow->copy()->addWeek();
        $endOfNextWeek = $endOfWeekNow->copy()->addWeek();
        $endOfMonthNow = $now->copy()->endOfMonth();

        // Determine the status based on the deadline
        if ($tenggatTugas->isPast()) {
            $status = 'Lewat'; // Status baru untuk tugas yang sudah lewat tenggat
        } elseif ($tenggatTugas->isToday()) {
            $status = 'Hari ini';
        } elseif ($tenggatTugas->isTomorrow()) {
            $status = 'Besok';
        } elseif ($tenggatTugas->between($startOfWeekNow, $endOfWeekNow)) {
            $status = 'Minggu ini';
        } elseif ($tenggatTugas->between($startOfNextWeek, $endOfNextWeek)) {
            $status = 'Minggu depan';
        } elseif ($tenggatTugas->month == $now->month) {
            $status = 'Bulan ini';
        }  else {
            $status = 'Diluar bulan ini';
        }

        // Tambahkan status ke dalam data yang divalidasi
        $validatedData['status'] = $status;

        // Tambahkan id_kelas_mata_pelajaran ke dalam data yang divalidasi
        $validatedData['id_kelas_mata_pelajaran'] = $kelasMapel->id_kelas_mata_pelajaran;

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
        
            // Ensure a valid uploaded file
            if ($file->isValid()) {
                // Generate a unique filename using timestamp and original name
                $filename = time() . '_' . $file->getClientOriginalName();
        
                // Store the file in the 'uploads/tugas' directory within the 'public' disk
                $path = $file->storeAs('uploads/tugas', $filename, 'public');
                
                // Add the file path to validated data
                $validatedData['file_path'] = $path;
            }
        }

        // Buat entri tugas baru
        $tugas = Tugas::create($validatedData);

        // Ambil semua siswa yang terdaftar di kelas ini dari KelasUser, di mana perannya adalah siswa
        $siswaKelas = KelasUser::where('id_kelas', $idKelas)
                            ->whereHas('user', function($query) {
                                $query->where('roles', 'siswa');
                            })
                            ->get();

        // Buat entri di TugasKelasMataPelajaran untuk setiap siswa yang ditemukan
        foreach ($siswaKelas as $siswa) {
            TugasKelasMataPelajaran::create([
                'id_tugas' => $tugas->id_tugas,
                'id_kelas_mata_pelajaran' => $kelasMapel->id_kelas_mata_pelajaran,
                'id_user' => $siswa->id_user,
                'status' => 'Belum mengumpulkan', // Atur status awal
            ]);
        }

        // Kembalikan respons sukses
        return response()->json([
            'success' => true,
            'data' => $tugas,
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Kembalikan respons kesalahan validasi
        return response()->json([
            'success' => false,
            'message' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        // Kembalikan respons kesalahan umum
        return response()->json([
            'success' => false,
            'message' => 'An error occurred',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
