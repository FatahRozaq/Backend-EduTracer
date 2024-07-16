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
    $request->validate([
        'nama_kelas' => 'required|string',
        'deskripsi' => 'nullable|string',
        'enrollment_key' => 'nullable|string',
        'id_user' => 'required|integer|exists:users,id',
    ]);

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

    // public function getKelasWithMataPelajaran(Request $request)
    // {
    //     // Dapatkan user yang sedang login
    //     $user = Auth::user();
        
    //     // Ambil kelas yang terkait dengan user
    //     $kelas = $user->kelas()->with('mataPelajaran')->get();

    //     // Kembalikan response dengan data kelas dan mata pelajaran
    //     return response()->json([
    //         'message' => 'Data kelas dan mata pelajaran berhasil diambil',
    //         'kelas' => $kelas,
    //     ], 200);
    // }

    // public function getMataPelajaran($id_kelas)
    // {
    //     // Dapatkan user yang sedang login
    //     $user = Auth::user();

    //     // Cek apakah user berhubungan dengan kelas ini
    //     $kelas = $user->kelas()->where('id_kelas', $id_kelas)->first();

    //     if (!$kelas) {
    //         return response()->json(['message' => 'User tidak berhubungan dengan kelas ini'], 403);
    //     }

    //     // Dapatkan mata pelajaran yang terkait dengan kelas
    //     $mataPelajaran = $kelas->mataPelajaran;

    //     return response()->json($mataPelajaran);
    // }
    
    // public function showMataPelajaran()
    // {
    //     $user = Auth::user();
    //     $mataPelajaran = $user->mataPelajaran();

    //     if ($mataPelajaran->isEmpty()) {
    //         return response()->json(['message' => 'No subjects found for your class.'], 404);
    //     }

    //     return response()->json($mataPelajaran);
    // }

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


}
