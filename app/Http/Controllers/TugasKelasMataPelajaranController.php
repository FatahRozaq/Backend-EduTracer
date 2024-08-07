<?php

namespace App\Http\Controllers;
use App\Models\TugasKelasMataPelajaran;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log; 

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

    public function getTugasById($id_tugas)
    {
        try {
            $tugas = TugasKelasMataPelajaran::where('id_tugas', $id_tugas)
                ->with(['tugas', 'kelasMataPelajaran', 'user'])
                ->get();

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

    public function getTugasByKelasMataPelajaran($id_kelas_mata_pelajaran)
    {
        try {
            $tugas = TugasKelasMataPelajaran::where('id_kelas_mata_pelajaran', $id_kelas_mata_pelajaran)
                ->with(['tugas', 'kelasMataPelajaran', 'user'])
                ->get();

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

    public function getTugasByKelasMataPelajaranAndTugas($id_kelas_mata_pelajaran, $id_tugas)
    {
        $user = Auth::user();
        try {
            $tugas = TugasKelasMataPelajaran::where('id_kelas_mata_pelajaran', $id_kelas_mata_pelajaran)
                                            ->where('id_tugas', $id_tugas)
                                            ->with(['tugas', 'kelasMataPelajaran', 'user'])
                                            ->get();

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

    // public function getTugasByKelasMataPelajaranAndUser($id_kelas_mata_pelajaran)
    // {
    //     try {
    //         $user = Auth::user();
    //         $userId = Auth::id();
    //         if (!$user) {
    //             return response()->json(['error' => 'Unauthorized'], 401);
    //         }

    //         $tugas = TugasKelasMataPelajaran::where('id_kelas_mata_pelajaran', $id_kelas_mata_pelajaran)
    //             ->where('id_user', $user->id)
    //             ->with(['tugas', 'kelasMataPelajaran', 'user'])
    //             ->get();

    //         return response()->json([
    //             'success' => true,
    //             'user' => $user->id,
    //             'data' => $tugas,
                
    //         ], 200);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Tugas not found',
    //         ], 404);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred',
                
    //         ], 500);
    //     }
    // }

    public function getTugasByKelasMataPelajaranAndUser($id_kelas_mata_pelajaran, $id_user)
    {
        try {
            $tugas = TugasKelasMataPelajaran::where('id_kelas_mata_pelajaran', $id_kelas_mata_pelajaran)
                ->where('id_user', $id_user)
                ->with(['tugas', 'kelasMataPelajaran', 'user'])
                ->get();

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
            Log::error('Error fetching Tugas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }



}
