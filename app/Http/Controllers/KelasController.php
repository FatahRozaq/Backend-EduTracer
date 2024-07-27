<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\KelasUser;
use App\Models\KelasMataPelajaran;
use App\Models\MataPelajaran;

class KelasController extends Controller
{
    
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_kelas' => 'required|string',
            'deskripsi' => 'nullable|string',
            'enrollment_key' => 'nullable|string|unique:kelas,enrollment_key',
            'id_user' => 'required|integer|exists:users,id',
        ]);

        try {
            $kelas = new Kelas();
            $kelas->nama_kelas = $request->nama_kelas;
            $kelas->deskripsi = $request->deskripsi;
            $kelas->enrollment_key = $request->enrollment_key;
            $kelas->wakel_id= $request->id_user;
            $kelas->save();
            
            $kelasUser = new KelasUser();
            $kelasUser->id_kelas = $kelas->id_kelas;
            $kelasUser->id_user = $request->id_user;
            $kelasUser->status = 'Confirm';
            $kelasUser->save();

            return response()->json($kelas, 201);
        } catch (\Illuminate\Database\QueryException $ex) {
            if ($ex->errorInfo[1] == 1062) { // Duplicate entry error code
                return response()->json(['message' => 'Enrollment key already exists'], 400);
            }
            return response()->json(['message' => 'Error creating class'], 500);
        }
    }

    
    public function getKelasByUserId(Request $request)
    {
        $user = Auth::user();

        $kelas = $user->kelas()->wherePivot('status', 'Confirm')->get();

        return response()->json($kelas, 200);
    }

    public function getKelasPending(Request $request)
    {
        $user = Auth::user();

        $kelas = $user->kelas()->wherePivot('status', 'Pending')->get();

        return response()->json($kelas, 200);
    }


    public function addMataPelajaran(Request $request, $id_kelas)
    {
        $request->validate([
            'id_mata_pelajaran' => 'required|exists:mata_pelajaran,id_mata_pelajaran',
        ]);

        $kelas = Kelas::findOrFail($id_kelas);

        KelasMataPelajaran::create([
            'id_kelas' => $id_kelas,
            'id_mata_pelajaran' => $request->id_mata_pelajaran,
        ]);

        // Kembalikan response
        return response()->json([
            'message' => 'Mata pelajaran berhasil ditambahkan ke kelas',
            'kelas' => $kelas,
        ], 201);
    }
    

    public function enroll(Request $request)
    {
        $request->validate([
            'enrollment_key' => 'required|string|exists:kelas,enrollment_key',
        ]);

        $user = Auth::user();

        $kelas = Kelas::where('enrollment_key', $request->enrollment_key)->first();

        if ($kelas->user()->where('id_user', $user->id)->exists()) {
            return response()->json([
                'message' => 'Anda sudah terdaftar di kelas ini.',
            ], 400);
        }
        KelasUser::create([
            'id_user' => $user->id,
            'id_kelas' => $kelas->id_kelas,
            'status' => 'Pending',
        ]);

        return response()->json([
            'message' => 'Anda berhasil mendaftar ke kelas.',
            'kelas' => $kelas,
        ], 201);
    }

    public function addMataPelajaranByKode(Request $request, $id_kelas)
    {
        // Validate the request
        $request->validate([
            'kode_mapel' => 'required|string|exists:mata_pelajaran,kode_mapel',
        ]);

        // Find the class
        $kelas = Kelas::findOrFail($id_kelas);

        // Find the subject by kode_mapel
        $mataPelajaran = MataPelajaran::where('kode_mapel', $request->kode_mapel)->firstOrFail();

        // Add the subject to the class
        KelasMataPelajaran::create([
            'id_kelas' => $kelas->id_kelas,
            'id_mata_pelajaran' => $mataPelajaran->id_mata_pelajaran,
        ]);

        // Return response
        return response()->json([
            'message' => 'Mata pelajaran berhasil ditambahkan ke kelas',
            'kelas' => $kelas,
            'mata_pelajaran' => $mataPelajaran,
        ], 201);
    }

    public function getMataPelajaran($id_kelas)
    {
        $kelas = Kelas::findOrFail($id_kelas);
        $mataPelajaran = $kelas->mataPelajaran;

        return response()->json($mataPelajaran);
    }

    public function destroy($id_kelas)
    {
        // Temukan kelas berdasarkan ID
        $kelas = Kelas::findOrFail($id_kelas);

        // Hapus semua relasi mata pelajaran terkait dengan kelas ini
        KelasMataPelajaran::where('id_kelas', $id_kelas)->delete();
        KelasUser::where('id_kelas', $id_kelas)->delete();

        // Hapus kelas
        $kelas->delete();

        return response()->json([
            'message' => 'Kelas dan relasi mata pelajaran berhasil dihapus.',
        ], 200);
    }

    public function update(Request $request, $id_kelas)
    {
        // Validasi data yang dikirimkan oleh permintaan
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'enrollment_key' => 'nullable|string|max:255|unique:kelas,enrollment_key,' . $id_kelas . ',id_kelas', 
        ]);

        // Temukan kelas berdasarkan ID
        $kelas = Kelas::findOrFail($id_kelas);

        // Perbarui data kelas dengan data dari permintaan
        $kelas->nama_kelas = $request->input('nama_kelas');
        $kelas->deskripsi = $request->input('deskripsi');
        $kelas->enrollment_key = $request->input('enrollment_key');

        // Simpan perubahan ke database
        $kelas->save();

        return response()->json([
            'message' => 'Kelas berhasil diperbarui',
            'kelas' => $kelas,
        ], 200);
    }

    public function destroyKelasUser($id_kelas)
    {
        // Dapatkan user yang sedang login
        $user = Auth::user();

        // Temukan relasi kelas_user berdasarkan id_kelas dan id_user yang sedang login
        $kelasUser = KelasUser::where('id_kelas', $id_kelas)
                            ->where('id_user', $user->id)
                            ->first();

        if ($kelasUser) {
            // Hapus relasi kelas_user
            $kelasUser->delete();

            return response()->json([
                'message' => 'KelasUser berhasil dihapus.',
            ], 200);
        } else {
            return response()->json([
                'message' => 'KelasUser tidak ditemukan.',
            ], 404);
        }
    }


    public function searchKelas(Request $request)
    {
        // Validasi input
        $request->validate([
            'search_term' => 'required|string|max:255',
        ]);

        // Ambil istilah pencarian dari permintaan
        $searchTerm = $request->input('search_term');

        // Cari kelas berdasarkan nama atau deskripsi yang sesuai dengan istilah pencarian
        $kelas = Kelas::where('nama_kelas', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('deskripsi', 'LIKE', "%{$searchTerm}%")
                        ->get();

        return response()->json([
            'message' => 'Pencarian berhasil.',
            'kelas' => $kelas,
        ], 200);
    }

    public function getKelasByLoggedInWakel()
    {
        $user = Auth::user();

        // Ambil kelas yang memiliki wakel_id sesuai dengan ID pengguna yang sedang login
        $kelas = Kelas::where('wakel_id', $user->id)->get();

        return response()->json($kelas);
    }



    public function getSiswaByClassId($id_kelas)
    {
        $kelas = Kelas::findOrFail($id_kelas);

        // Get users with the role 'Siswa' connected to the class and include the pivot table status
        $siswa = $kelas->user()->where('roles', 'Siswa')->withPivot('status')->get();


        return response()->json($siswa, 200);
    }

    public function getUserByClassId($id_kelas)
    {
        $kelas = Kelas::findOrFail($id_kelas);

        $user = $kelas->user()->withPivot('status')->get();



        return response()->json($user, 200);
    }

    public function getPendingStudentsByClassId($id_kelas)
    {
        $kelas = Kelas::findOrFail($id_kelas);

        // Get users with the status 'Pending' connected to the class
        // $siswa = $kelas->user()->where('roles', 'Siswa')->wherePivot('status', 'Pending')->get();
        $siswa = $kelas->user()->where('roles', 'Siswa')->withPivot('status', 'Pending')->get();

        return response()->json($siswa, 200);
    }

    public function confirmStudent(Request $request, $id_kelas)
    {
        $validatedData = $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        $kelasUser = KelasUser::where('id_kelas', $id_kelas)
            ->where('id_user', $validatedData['student_id'])
            ->firstOrFail();

        $kelasUser->status = 'Confirm';
        $kelasUser->save();

        return response()->json(['message' => 'Student confirmed'], 200);
    }


    public function getGuruInClass(Request $request, $id_kelas)
    {
        $kelas = Kelas::find($id_kelas);
    
        if (!$kelas) {
            return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
        }
    
        $guruList = $kelas->user()->where('roles', 'Guru')->get();
    
        return response()->json($guruList, 200);
    }


}
