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
        ]);

        $kelas = new Kelas();
        $kelas->nama_kelas = $request->nama_kelas;
        $kelas->deskripsi = $request->deskripsi;
        $kelas->enrollment_key = $request->enrollment_key;

        
        $kelas->save();
        
        $kelasUser = new KelasUser();
        $kelasUser->id_kelas = $kelas->id_kelas;
        $kelasUser->id_user = Auth::id(); 

        
        $kelasUser->save();

        // if (Auth::check()) {
            
        //     $kelasUser = new KelasUser();
        //     $kelasUser->id_kelas = $kelas->id_kelas;
        //     $kelasUser->id_user = Auth::id(); 

            
        //     $kelasUser->save();
        // } else {
            
        //     return response()->json(['message' => 'Unauthorized'], 401);
        // }

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
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Mendapatkan kelas yang terkait dengan user
        $kelas = $user->kelas;

        // Mengembalikan response dalam bentuk JSON
        return response()->json($kelas, 200);
    }

    public function create(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'enrollment_key' => 'nullable|string|max:255',
        ]);

        // Buat kelas baru
        $kelas = Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'deskripsi' => $request->deskripsi,
            'enrollment_key' => $request->enrollment_key,
        ]);

        // Dapatkan user yang sedang login
        $user = Auth::user();

        // Tambahkan relasi ke kelas_user
        KelasUser::create([
            'id_user' => $user->id,
            'id_kelas' => $kelas->id_kelas,
        ]);

        // Kembalikan response
        return response()->json([
            'message' => 'Kelas berhasil dibuat',
            'kelas' => $kelas,
        ], 201);
    }

    public function addMataPelajaran(Request $request, $id_kelas)
    {
        // Validasi input
        $request->validate([
            'id_mata_pelajaran' => 'required|exists:mata_pelajarans,id_mata_pelajaran',
        ]);

        // Cek apakah kelas ada
        $kelas = Kelas::findOrFail($id_kelas);

        // Tambahkan relasi ke kelas_mata_pelajaran
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


}
