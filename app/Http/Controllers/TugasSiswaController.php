<?php

namespace App\Http\Controllers;

use App\Models\KelasMataPelajaran;
use App\Models\Tugas;
use Illuminate\Http\Request;
use App\Models\TugasKelasMataPelajaran;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TugasSiswaController extends Controller
{
    public function __construct()
    {
        Carbon::setLocale('id');
    }

    private function getCurrentTime()
    {
        return Carbon::now('Asia/Jakarta');
    }

    public function index($idKelas)
    {
    try {
        // Mengambil id_kelas_mata_pelajaran berdasarkan id_kelas
        $kelas = KelasMataPelajaran::where('id_kelas', $idKelas)->pluck('id_kelas_mata_pelajaran');
        
        // Mengambil tugas dan melakukan eager loading untuk relasi kelas dan mata pelajaran
        $tugas = Tugas::with(['kelasMataPelajaran.kelas', 'kelasMataPelajaran.mataPelajaran'])
            ->whereIn('id_kelas_mata_pelajaran', $kelas)
            ->get();

        // Ambil waktu saat ini
        $now = $this->getCurrentTime();
        $startOfWeekNow = $now->copy()->startOfWeek();
        $endOfWeekNow = $now->copy()->endOfWeek();
        $startOfNextWeek = $startOfWeekNow->copy()->addWeek();
        $endOfNextWeek = $endOfWeekNow->copy()->addWeek();
        $endOfMonthNow = $now->copy()->endOfMonth();

        // Loop untuk memperbarui status tugas
        foreach ($tugas as $item) {
            $tenggatTugas = Carbon::parse($item->tenggat_tugas, 'Asia/Jakarta');

            if ($tenggatTugas->isPast()) {
                $item->status = 'Lewat'; // Status baru untuk tugas yang sudah lewat tenggat
            } elseif ($tenggatTugas->isToday()) {
                $item->status = 'Hari ini';
            } elseif ($tenggatTugas->isTomorrow()) {
                $item->status = 'Besok';
            } elseif ($tenggatTugas->between($startOfWeekNow, $endOfWeekNow)) {
                $item->status = 'Minggu ini';
            } elseif ($tenggatTugas->between($startOfNextWeek, $endOfNextWeek)) {
                $item->status = 'Minggu depan';
            } elseif ($tenggatTugas->month == $now->month) {
                $item->status = 'Bulan ini';
            } else {
                $item->status = 'Diluar bulan ini';
            }

            // Simpan perubahan status
            $item->save();
        }

        // Mengurutkan berdasarkan status dan kemudian tenggat_tugas
        $sortedData = $tugas->sortBy(function ($item) {
            $statusOrder = [
                'Lewat' => 1,
                'Hari ini' => 2,
                'Besok' => 3,
                'Minggu ini' => 4,
                'Minggu depan' => 5,
                'Bulan ini' => 6,
                'Diluar bulan ini' => 7,
            ];
            return $statusOrder[$item->status] ?? 8;
        })->sortBy('tenggat_tugas')->values(); // Mengurutkan berdasarkan tenggat_tugas setelah sorting status

        // Membentuk data response dengan tugas, nama kelas, dan nama mata pelajaran
        $data = $sortedData->map(function ($item) {
            return [
                'id_tugas' => $item->id_tugas,
                'nama_tugas' => $item->nama_tugas,
                'deskripsi' => $item->deskripsi,
                'tenggat_tugas' => $item->tenggat_tugas,
                'status' => $item->status,
                'file' => $item->file,
                'file_path' => $item->file_path,
                'nama_kelas' => $item->kelasMataPelajaran->kelas->nama_kelas ?? null,
                'nama_mapel' => $item->kelasMataPelajaran->mataPelajaran->nama_mapel ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data tugas berhasil diambil',
            'data' => $data
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve data',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function showById($idTugas)
    {
        try {
            // Mengambil tugas berdasarkan id_tugas dengan eager loading untuk relasi kelas dan mata pelajaran
            $tugas = Tugas::with(['kelasMataPelajaran.kelas', 'kelasMataPelajaran.mataPelajaran'])
                ->findOrFail($idTugas);
            
            // Membentuk data response dengan tugas, nama kelas, dan nama mata pelajaran
            $data = [
                'id_tugas' => $tugas->id_tugas,
                'nama_tugas' => $tugas->nama_tugas,
                'deskripsi' => $tugas->deskripsi,
                'tenggat_tugas' => $tugas->tenggat_tugas,
                'status' => $tugas->status,
                'file' => $tugas->file,
                'file_path' => $tugas->file_path,
                'id_kelas_mata_pelajaran' => $tugas->id_kelas_mata_pelajaran,
                'nama_kelas' => $tugas->kelasMataPelajaran->kelas->nama_kelas ?? null,
                'nama_mapel' => $tugas->kelasMataPelajaran->mataPelajaran->nama_mapel ?? null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detail tugas berhasil diambil',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage()
            ], 500);
        }
    }





    // Show data by ID
    public function show($id)
    {
        try {
            $data = TugasKelasMataPelajaran::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_tugas' => 'required|integer',
            'id_kelas_mata_pelajaran' => 'required|integer',
            'id_user' => 'required|integer',
            'status' => 'required|string',
            'berkas' => 'nullable|file|max:4096', // 4MB in kilobytes
            'nilai_tugas' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $data = $request->all();

            if ($request->hasFile('berkas')) {
                $file = $request->file('berkas');
            
                // Ensure a valid uploaded file
                if ($file->isValid()) {
                    // Generate a unique filename using timestamp and original name
                    $filename = $file->getClientOriginalName();
            
                    // Store the file in the 'uploads/tugas' directory within the 'public' disk
                    $path = $file->storeAs('uploads/tugas_siswa', $filename, 'public');

                    $data['berkas'] = $path;
                }
            }

            $tugasKelasMataPelajaran = TugasKelasMataPelajaran::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Data successfully saved',
                'data' => $tugasKelasMataPelajaran
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data failed to save',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_tugas' => 'required|integer',
            'id_kelas_mata_pelajaran' => 'required|integer',
            'id_user' => 'required|integer',
            'status' => 'required|string',
            'berkas' => 'nullable|file|max:4096', // 4MB in kilobytes
            'nilai_tugas' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $tugasKelasMataPelajaran = TugasKelasMataPelajaran::findOrFail($id);

            $data = $request->all();

            if ($request->hasFile('berkas')) {
                // Delete the old file if it exists
                if ($tugasKelasMataPelajaran->berkas) {
                    Storage::delete($tugasKelasMataPelajaran->berkas);
                }

                $file = $request->file('berkas');
                $filePath = $file->store('public/tugas_files'); // Store the file in the 'public/tugas_files' directory
                $data['berkas'] = $filePath;
            }

            $tugasKelasMataPelajaran->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data successfully updated',
                'data' => $tugasKelasMataPelajaran
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data failed to update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete data
    public function destroy($id)
    {
        try {
            $tugasKelasMataPelajaran = TugasKelasMataPelajaran::findOrFail($id);

            // Delete the file if it exists
            if ($tugasKelasMataPelajaran->berkas) {
                Storage::delete($tugasKelasMataPelajaran->berkas);
            }

            $tugasKelasMataPelajaran->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data successfully deleted'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data failed to delete',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateNilaiTugas(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nilai_tugas' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $tugasKelasMataPelajaran = TugasKelasMataPelajaran::findOrFail($id);
            $tugasKelasMataPelajaran->nilai_tugas = $request->input('nilai_tugas');
            $tugasKelasMataPelajaran->save();

            return response()->json([
                'success' => true,
                'message' => 'Nilai tugas successfully updated',
                'data' => $tugasKelasMataPelajaran
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update nilai tugas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function indexBaru($idKelas, $idUser)
    {
        try {
            // Mengambil id_kelas_mata_pelajaran berdasarkan id_kelas
            $kelasMataPelajaran = KelasMataPelajaran::where('id_kelas', $idKelas)->pluck('id_kelas_mata_pelajaran');
            
            // Mengambil tugas_kelas_mata_pelajaran dan melakukan eager loading untuk relasi tugas, kelas, dan mata pelajaran
            $tugasKelasMataPelajaran = TugasKelasMataPelajaran::with([
                'tugas.kelasMataPelajaran.kelas',
                'tugas.kelasMataPelajaran.mataPelajaran'
            ])
            ->whereIn('id_kelas_mata_pelajaran', $kelasMataPelajaran)
            ->where('id_user', $idUser) // Filter berdasarkan id_user
            ->get();

            // Ambil waktu saat ini
            $now = $this->getCurrentTime();
            $startOfWeekNow = $now->copy()->startOfWeek();
            $endOfWeekNow = $now->copy()->endOfWeek();
            $startOfNextWeek = $startOfWeekNow->copy()->addWeek();
            $endOfNextWeek = $endOfWeekNow->copy()->addWeek();
            $endOfMonthNow = $now->copy()->endOfMonth();

            // Loop untuk memperbarui status tugas
            foreach ($tugasKelasMataPelajaran as $item) {
                $tenggatTugas = Carbon::parse($item->tugas->tenggat_tugas, 'Asia/Jakarta');

                if ($tenggatTugas->isPast()) {
                    $item->tugas->status = 'Lewat'; // Status baru untuk tugas yang sudah lewat tenggat
                } elseif ($tenggatTugas->isToday()) {
                    $item->tugas->status = 'Hari ini';
                } elseif ($tenggatTugas->isTomorrow()) {
                    $item->tugas->status = 'Besok';
                } elseif ($tenggatTugas->between($startOfWeekNow, $endOfWeekNow)) {
                    $item->tugas->status = 'Minggu ini';
                } elseif ($tenggatTugas->between($startOfNextWeek, $endOfNextWeek)) {
                    $item->tugas->status = 'Minggu depan';
                } elseif ($tenggatTugas->month == $now->month) {
                    $item->tugas->status = 'Bulan ini';
                } else {
                    $item->tugas->status = 'Diluar bulan ini';
                }

                // Simpan perubahan status
                $item->tugas->save();
            }

            // Mengurutkan berdasarkan status dan kemudian tenggat_tugas
            $sortedData = $tugasKelasMataPelajaran->sortBy(function ($item) {
                $statusOrder = [
                    'Lewat' => 1,
                    'Hari ini' => 2,
                    'Besok' => 3,
                    'Minggu ini' => 4,
                    'Minggu depan' => 5,
                    'Bulan ini' => 6,
                    'Diluar bulan ini' => 7,
                ];
                return $statusOrder[$item->tugas->status] ?? 8;
            })->sortBy('tugas.tenggat_tugas')->values(); // Mengurutkan berdasarkan tenggat_tugas setelah sorting status

            // Membentuk data response dengan tugas, nama kelas, dan nama mata pelajaran
            $data = $sortedData->map(function ($item) {
                return [
                    'id_tugas_kelas_mata_pelajaran' => $item->id_tugas_kelas_mata_pelajaran,
                    'id_tugas' => $item->tugas->id_tugas,
                    'nama_tugas' => $item->tugas->nama_tugas,
                    'deskripsi' => $item->tugas->deskripsi,
                    'tenggat_tugas' => $item->tugas->tenggat_tugas,
                    'status' => $item->tugas->status,
                    'file' => $item->tugas->file,
                    'file_path' => $item->tugas->file_path,
                    'nilai_tugas' => $item->nilai_tugas,
                    'id_user' => $item->id_user, // Menampilkan id_user
                    'status_pengumpulan' => $item->status, // Menampilkan status sebagai status_pengumpulan
                    'berkas' => $item->berkas, // Menampilkan berkas
                    'nama_kelas' => $item->tugas->kelasMataPelajaran->kelas->nama_kelas ?? null,
                    'nama_mapel' => $item->tugas->kelasMataPelajaran->mataPelajaran->nama_mapel ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data tugas berhasil diambil',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pengumpulanTugas(Request $request, $idTugas)
    {
        try {
            // Validasi data request
            $validatedData = $request->validate([
                'berkas' => 'required|file|max:4096|mimes:jpg,jpeg,png,pdf,doc,docx'
            ]);


            // Cari entri TugasKelasMataPelajaran berdasarkan id_tugas_kelas_mata_pelajaran
            $tugasKelasMataPelajaran = TugasKelasMataPelajaran::find($idTugas);

            // Periksa apakah entri ditemukan
            if (!$tugasKelasMataPelajaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas Kelas Mata Pelajaran tidak ditemukan.',
                ], 404);
            }

            // Handle file upload
            if ($request->hasFile('berkas')) {
                $file = $request->file('berkas');
            
                // Ensure a valid uploaded file
                if ($file->isValid()) {
                    // Generate a unique filename using timestamp and original name
                    $filename = $file->getClientOriginalName();
            
                    // Store the file in the 'uploads/tugas_siswa' directory within the 'public' disk
                    $path = $file->storeAs('uploads/tugas_siswa', $filename, 'public');
                    
                    // Add the file path to the model
                    $tugasKelasMataPelajaran->berkas = $path;
                }
            } else {
                return response()->json("Gagal");   
            }

            // Set the status to 'Sudah mengumpulkan'
            $tugasKelasMataPelajaran->status = 'Sudah mengumpulkan';

            // Simpan perubahan
            $tugasKelasMataPelajaran->save();

            // Kembalikan respons sukses
            return response()->json([
                'success' => true,
                'message' => 'Berkas berhasil diunggah dan status diperbarui.',
                'data' => $tugasKelasMataPelajaran
            ], 200);

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
