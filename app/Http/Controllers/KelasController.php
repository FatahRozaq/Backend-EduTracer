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
            $kelas->save();
            
            $kelasUser = new KelasUser();
            $kelasUser->id_kelas = $kelas->id_kelas;
            $kelasUser->id_user = $request->id_user;
            $kelasUser->save();

            return response()->json($kelas, 201);
        } catch (\Illuminate\Database\QueryException $ex) {
            if ($ex->errorInfo[1] == 1062) { // Duplicate entry error code
                return response()->json(['message' => 'Enrollment key already exists'], 400);
            }
            return response()->json(['message' => 'Error creating class'], 500);
        }
    }




    public function index()
    {
        if (Auth::check()) {
            
            $userId = Auth::id();

            $kelas = Kelas::whereHas('user', function ($query) use ($userId) {
                $query->where('id_user', $userId);
            })->get();
            return response()->json($kelas);
        } else {
            
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
    
    public function getKelasByUserId(Request $request)
    {
        $user = Auth::user();

        $kelas = $user->kelas;

        return response()->json($kelas, 200);
    }

    public function create(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'enrollment_key' => 'nullable|string|max:255|unique:kelas,enrollment_key',
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'deskripsi' => $request->deskripsi,
            'enrollment_key' => $request->enrollment_key,
        ]);

        $user = Auth::user();

        KelasUser::create([
            'id_user' => $user->id,
            'id_kelas' => $kelas->id_kelas,
        ]);

        return response()->json([
            'message' => 'Kelas berhasil dibuat',
            'kelas' => $kelas,
        ], 201);
    }

    public function addMataPelajaran(Request $request, $id_kelas)
    {
        $request->validate([
            'id_mata_pelajaran' => 'required|exists:mata_pelajarans,id_mata_pelajaran',
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
        ]);

        return response()->json([
            'message' => 'Anda berhasil mendaftar ke kelas.',
            'kelas' => $kelas,
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




}
