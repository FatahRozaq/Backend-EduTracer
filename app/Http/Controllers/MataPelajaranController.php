<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\KelasUser;
use App\Models\KelasMataPelajaran;
use App\Models\MataPelajaran;

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

        // Kembalikan response
        return response()->json([
            'message' => 'Mata pelajaran berhasil dibuat dan ditambahkan ke kelas',
            'mata_pelajaran' => $mataPelajaran,
        ], 201);
    }

    public function destroyKelasMataPelajaran($id_kelas, $id_mata_pelajaran)
    {
        // Temukan relasi kelas_mata_pelajaran berdasarkan id_kelas dan id_mata_pelajaran
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



}
