<?php

namespace App\Http\Controllers;
use App\Models\TugasKelasMataPelajaran;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TugasKelasMataPelajaranController extends Controller
{
    public function getTugasByUser()
    {
        $userId = Auth::id();
        $tugas = TugasKelasMataPelajaran::with(['tugas', 'kelasMataPelajaran'])
                                        ->where('id_user', $userId)
                                        ->get();

        return response()->json($tugas, 200);
    }

    public function searchTugas(Request $request)
    {
        // Validasi data input
        $request->validate([
            'search_term' => 'required|string|max:255',
        ]);

        $searchTerm = $request->input('search_term');
        
        $tugas = TugasKelasMataPelajaran::with(['tugas', 'kelasMataPelajaran.mataPelajaran'])
                ->whereHas('tugas', function($query) use ($searchTerm) {
                    $query->where('nama_tugas', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('kelasMataPelajaran.mataPelajaran', function($query) use ($searchTerm) {
                    $query->where('nama_mapel', 'LIKE', "%{$searchTerm}%");
                })
                ->get();

        return response()->json($tugas, 200);
    }

    public function updateTugasKelasMataPelajaran(Request $request, $id)
    {
        $tugasKelasMataPelajaran = TugasKelasMataPelajaran::findOrFail($id);

        $validatedData = $request->validate([
            'status' => 'required|string',
            'berkas' => 'nullable|string',
            'nilai_tugas' => 'nullable|numeric',
        ]);

        $tugasKelasMataPelajaran->update($validatedData);

        return response()->json($tugasKelasMataPelajaran, 200);
    }
}
