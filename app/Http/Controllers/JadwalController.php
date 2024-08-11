<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jadwal;
use App\Models\KelasUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\PengajarMapel;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $kelasId = $request->query('kelas_id');

        $jadwals = Jadwal::with(['kelas', 'mataPelajaran'])
            ->where('id_kelas', $kelasId)
            ->get();

        return response()->json($jadwals);
    }

    public function getJadwalPengajar()
    {
        $userId = Auth::id();
        $jadwals = Jadwal::with(['kelas', 'mataPelajaran'])
            ->whereHas('mataPelajaran.pengajar', function ($query) use ($userId) {
                $query->where('id_user', $userId);
            })
            ->get();

        return response()->json($jadwals);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_mata_pelajaran' => 'required|exists:mata_pelajaran,id_mata_pelajaran',
            'hari' => 'required|string',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_akhir' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        $overlappingJadwal = Jadwal::where('id_kelas', $request->id_kelas)
            ->where('hari', $request->hari)
            ->where(function ($query) use ($request) {
                $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_akhir])
                    ->orWhereBetween('jam_akhir', [$request->jam_mulai, $request->jam_akhir])
                    ->orWhere(function ($query) use ($request) {
                        $query->where('jam_mulai', '<', $request->jam_mulai)
                            ->where('jam_akhir', '>', $request->jam_akhir);
                    });
            })
            ->exists();

        if ($overlappingJadwal) {
            return response()->json(['message' => 'Jadwal tumpang tindih dengan jadwal yang sudah ada'], 409);
        }

        $jadwal = Jadwal::create([
            'id_kelas' => $request->id_kelas,
            'id_mata_pelajaran' => $request->id_mata_pelajaran,
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_akhir' => $request->jam_akhir,
        ]);

        $pengajarMapel = PengajarMapel::where('id_mata_pelajaran', $request->id_mata_pelajaran)
                                      ->where('id_kelas', $request->id_kelas)
                                      ->get();
        foreach ($pengajarMapel as $pengajar) {
            $jadwal->pengajarMapel()->attach($pengajar->id_user);
        }

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
        $userId = Auth::id();

        $kelasUser = KelasUser::where('id_user', $userId)
            ->where('id_kelas', $jadwal->id_kelas)
            ->where('status', 'Confirm')
            ->first();

        if (!$kelasUser) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengubah jadwal pada kelas ini'], 403);
        }

        $overlappingJadwal = Jadwal::where('id_kelas', $request->id_kelas)
            ->where('hari', $request->hari)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_akhir])
                    ->orWhereBetween('jam_akhir', [$request->jam_mulai, $request->jam_akhir])
                    ->orWhere(function ($query) use ($request) {
                        $query->where('jam_mulai', '<', $request->jam_mulai)
                            ->where('jam_akhir', '>', $request->jam_akhir);
                    });
            })
            ->exists();

        if ($overlappingJadwal) {
            return response()->json(['message' => 'Jadwal tumpang tindih dengan jadwal yang sudah ada'], 409);
        }

        $jadwal->update($request->all());

        return response()->json($jadwal);
    }

    public function destroy($id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $userId = Auth::id();

        $kelasUser = KelasUser::where('id_user', $userId)
            ->where('id_kelas', $jadwal->id_kelas)
            ->where('status', 'Confirm')
            ->first();

        if (!$kelasUser) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus jadwal pada kelas ini'], 403);
        }

        $jadwal->delete();

        return response()->json(null, 204);
    }
}
