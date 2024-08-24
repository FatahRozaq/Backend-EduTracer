<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use Illuminate\Http\Request;
use App\Models\PengajarMapel;
use App\Models\JadwalPengajar;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function store(Request $request)
{
    try {
        $overlappingJadwal = Jadwal::where('id_kelas', $request->id_kelas)
            ->where('hari', $request->hari)
            ->where(function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('jam_mulai', '<', $request->jam_akhir)
                        ->where('jam_akhir', '>', $request->jam_mulai);
                });
            })
            ->exists();

        if ($overlappingJadwal) {
            return response()->json(['message' => 'Jadwal tumpang tindih dengan jadwal yang sudah ada'], 409);
        }

        $idKelas = $request['id_kelas'];
        $idMataPelajaran = $request['id_mata_pelajaran'];

        $pengajarMapel = PengajarMapel::where('id_kelas', $idKelas)
            ->where('id_mata_pelajaran', $idMataPelajaran)
            ->pluck('id_user');

        $jadwal = Jadwal::create($request->all());

        foreach ($pengajarMapel as $idUser) {
            JadwalPengajar::create([
                'id_jadwal' => $jadwal->id_jadwal, 
                'id_user' => $idUser,
            ]);
        }

        return response()->json($jadwal, 201);
    } catch (\Exception $e) {
        Log::error('Gagal menambahkan jadwal: ' . $e->getMessage());
        return response()->json(['message' => 'Gagal menambahkan jadwal karena masalah internal.'], 500);
    }
}

}
