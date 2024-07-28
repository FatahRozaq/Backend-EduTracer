<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jadwal;
use App\Models\KelasUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $kelasId = $request->query('kelas_id');
        
        Log::info('Fetching jadwals for user: ' . $userId . ' in class: ' . $kelasId);
        
        $jadwals = Jadwal::with(['kelas', 'mataPelajaran'])
            ->where('id_kelas', $kelasId)
            ->get();

        Log::info('Jadwals fetched: ', $jadwals->toArray());

        return response()->json($jadwals);
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $kelasUser = KelasUser::where('id_user', $userId)
            ->where('id_kelas', $request->id_kelas)
            ->where('status', 'Confirm')
            ->first();

        if (!$kelasUser) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menambahkan jadwal pada kelas ini'], 403);
        }

        $request->validate([
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_mata_pelajaran' => 'required|exists:mata_pelajaran,id_mata_pelajaran',
            'hari' => 'required|string',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_akhir' => 'required|date_format:H:i|after:jam_mulai',
        ]);


        $jadwal = Jadwal::create([
            'id_kelas' => $request->id_kelas,
            'id_mata_pelajaran' => $request->id_mata_pelajaran,
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_akhir' => $request->jam_akhir,
        ]);

        $jadwal->load('kelas', 'mataPelajaran');

        return response()->json($jadwal, 201);
    }


    public function show($id)
    {
        $jadwal = Jadwal::with(['kelas', 'mataPelajaran'])->findOrFail($id);
        return response()->json($jadwal);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_mata_pelajaran' => 'required|exists:mata_pelajaran,id_mata_pelajaran',
            'hari' => 'required|string',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_akhir' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        $jadwal = Jadwal::findOrFail($id);
        $jadwal->update($request->all());

        return response()->json($jadwal);
    }

    public function destroy($id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->delete();

        return response()->json(null, 204);
    }
}
