<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\KelasUser;
use App\Models\KelasMataPelajaran;
use App\Models\MataPelajaran;
use App\Models\PengajarMapel;

class MataPelajaranController extends Controller
{
    
    public function storeMataPelajaran(Request $request, $id_kelas)
    {
        // Validasi data input
        $request->validate([
            'kode_mapel' => 'required|string|max:255',
            'nama_mapel' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'id_user' => 'required|integer|exists:users,id',
        ]);

        // Buat mata pelajaran baru
        $mataPelajaran = MataPelajaran::create([
            'kode_mapel' => $request->kode_mapel,
            'nama_mapel' => $request->nama_mapel,
            'deskripsi' => $request->deskripsi,
            'id_user' => $request->id_user,
        ]);

        // Tambahkan mata pelajaran ke kelas
        KelasMataPelajaran::create([
            'id_kelas' => $id_kelas,
            'id_mata_pelajaran' => $mataPelajaran->id_mata_pelajaran,
        ]);

        //kelas user
        KelasUser::create([
            'id_kelas' => $id_kelas,
            'id_user' => $request->id_user,
            'status' => 'Confirm',
        ]);

        // Buat relasi dengan mapel guru
        PengajarMapel::create([
            'id_mata_pelajaran' => $mataPelajaran->id_mata_pelajaran,
            'id_user' => $request->id_user,
        ]);

        // Kembalikan response
        return response()->json([
            'message' => 'Mata pelajaran berhasil dibuat dan ditambahkan ke kelas, serta relasi guru berhasil dibuat',
            'mata_pelajaran' => $mataPelajaran,
        ], 201);
    }

    public function destroyKelasMataPelajaran($id_kelas, $id_mata_pelajaran)
    {
        $kelasMataPelajaran = KelasMataPelajaran::where('id_kelas', $id_kelas)
                                                ->where('id_mata_pelajaran', $id_mata_pelajaran)
                                                ->first();

        if ($kelasMataPelajaran) {
            // Hapus relasi kelas_mata_pelajaran
            $kelasMataPelajaran->delete();

            return response()->json([
                'message' => 'KelasMataPelajaran berhasil dihapus.',
            ], 200);
        } else {
            return response()->json([
                'message' => 'KelasMataPelajaran tidak ditemukan.',
            ], 404);
        }
    }

    public function searchMataPelajaran(Request $request)
    {
        // Validasi data input
        $request->validate([
            'search_term' => 'required|string|max:255',
        ]);

        // Ambil istilah pencarian dari permintaan
        $searchTerm = $request->input('search_term');

        // Cari mata pelajaran berdasarkan nama atau kode mapel yang sesuai dengan istilah pencarian
        $mataPelajaran = MataPelajaran::where('nama_mapel', 'LIKE', "%{$searchTerm}%")
                                    ->orWhere('kode_mapel', 'LIKE', "%{$searchTerm}%")
                                    ->get();

        return response()->json([
            'message' => 'Pencarian berhasil.',
            'mata_pelajaran' => $mataPelajaran,
        ], 200);
    }

    public function updateMataPelajaran(Request $request, $id_mata_pelajaran)
    {
        // Validasi data input
        $request->validate([
            'kode_mapel' => 'required|string|max:255',
            'nama_mapel' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        // Temukan mata pelajaran berdasarkan ID
        $mataPelajaran = MataPelajaran::findOrFail($id_mata_pelajaran);

        // Perbarui data mata pelajaran
        $mataPelajaran->kode_mapel = $request->kode_mapel;
        $mataPelajaran->nama_mapel = $request->nama_mapel;
        $mataPelajaran->deskripsi = $request->deskripsi;

        // Simpan perubahan ke database
        $mataPelajaran->save();

        return response()->json([
            'message' => 'Mata pelajaran berhasil diperbarui',
            'mata_pelajaran' => $mataPelajaran,
        ], 200);
    }

    

    public function destroyMataPelajaran($id_mata_pelajaran)
    {
        // Temukan mata pelajaran berdasarkan ID
        $mataPelajaran = MataPelajaran::findOrFail($id_mata_pelajaran);

        // Hapus semua relasi kelas-mata pelajaran terkait dengan mata pelajaran ini
        KelasMataPelajaran::where('id_mata_pelajaran', $id_mata_pelajaran)->delete();

        // Hapus mata pelajaran
        $mataPelajaran->delete();

        return response()->json([
            'message' => 'Mata pelajaran dan semua relasi kelas-mata pelajaran berhasil dihapus.',
        ], 200);
    }

    

    public function getMataPelajaranByLoggedInUser()
    {
        $userId = Auth::id();

        // $mataPelajaran = PengajarMapel::where('id_user', $userId)
        //                           ->with('mataPelajaran')
        //                           ->get()
        //                           ->pluck('mataPelajaran');
        $mataPelajaran = MataPelajaran::where('id_user', $userId)->get();

        return response()->json($mataPelajaran, 200);
    }

    public function getMataPelajaranByLoggedInPengajar()
    {
        $userId = Auth::id();

        $mataPelajaran = PengajarMapel::where('id_user', $userId)
                                  ->with('mataPelajaran')
                                  ->get()
                                  ->pluck('mataPelajaran');
        // $mataPelajaran = MataPelajaran::where('id_user', $userId)->get();

        return response()->json($mataPelajaran, 200);
    }

    public function getMataPelajaranByLoggedInPengajarAndClass($id_kelas)
    {
        $userId = Auth::id();

        $mataPelajaran = PengajarMapel::where('id_user', $userId)
                                    ->where('id_kelas', $id_kelas)
                                    ->with('mataPelajaran')
                                    ->get()
                                    ->pluck('mataPelajaran');

        return response()->json($mataPelajaran, 200);
    }

    public function getPengajarByMataPelajaran($id_mata_pelajaran)
    {
        $mataPelajaran = MataPelajaran::findOrFail($id_mata_pelajaran);

        $pengajar = $mataPelajaran->pengajar()->with('user')->get();

        return response()->json([
            'message' => 'Pengajar fetched successfully',
            'pengajar' => $pengajar,
        ], 200);
    }


    public function storeMataPelajaranonly(Request $request)
    {
        $request->validate([
            'kode_mapel' => 'required|string|max:255|unique:mata_pelajaran,kode_mapel',
            'nama_mapel' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'id_user' => 'required|integer|exists:users,id',
        ]);

        
        $mataPelajaran = MataPelajaran::create([
            'kode_mapel' => $request->kode_mapel,
            'nama_mapel' => $request->nama_mapel,
            'deskripsi' => $request->deskripsi,
            'id_user' => $request->id_user,
        ]);

        return response()->json([
            'message' => 'Mata pelajaran berhasil dibuat dan ditambahkan ke kelas, serta relasi guru berhasil dibuat',
            'mata_pelajaran' => $mataPelajaran,
        ], 201);
    }

    public function addPengajarToMataPelajaran(Request $request, $mataPelajaranId)
    {
        $validatedData = $request->validate([
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_user' => 'required|exists:users,id',
        ]);

        $existingAssignment = PengajarMapel::where('id_mata_pelajaran', $mataPelajaranId)
                                           ->where('id_kelas', $validatedData['id_kelas'])
                                           ->where('id_user', $validatedData['id_user'])
                                           ->first();

        if ($existingAssignment) {
            return response()->json(['message' => 'Pengajar already assigned to this class and subject'], 409);
        }

        PengajarMapel::create([
            'id_mata_pelajaran' => $mataPelajaranId,
            'id_kelas' => $validatedData['id_kelas'],
            'id_user' => $validatedData['id_user'],
        ]);

        KelasUser::updateOrCreate(
            ['id_kelas' => $validatedData['id_kelas'], 'id_user' => $validatedData['id_user']],
            ['status' => 'Confirm']
        );

        KelasMataPelajaran::create([
            'id_mata_pelajaran' => $mataPelajaranId,
            'id_kelas' => $validatedData['id_kelas'],
        ]);

        return response()->json(['message' => 'Pengajar added successfully'], 201);
    }

    public function getKelasMataPelajaranId($id_kelas, $id_mata_pelajaran)
    {
        try {
            $kelasMataPelajaran = KelasMataPelajaran::where('id_kelas', $id_kelas)
                                                    ->where('id_mata_pelajaran', $id_mata_pelajaran)
                                                    ->first();

            if ($kelasMataPelajaran) {
                return response()->json([
                    'success' => true,
                    'data' => $kelasMataPelajaran->id_kelas_mata_pelajaran,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'KelasMataPelajaran not found.',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }


}
