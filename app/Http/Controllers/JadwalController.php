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

    public function store(Request $request)
    {
        $request->validate([
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_mata_pelajaran' => 'required|exists:mata_pelajaran,id_mata_pelajaran',
            'hari' => 'required|string',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_akhir' => 'required|date_format:H:i',
        ]);

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

            $jadwal = Jadwal::create($request->all());

            return response()->json($jadwal, 201);
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan jadwal: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menambahkan jadwal karena masalah internal.'], 500);
        }
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

        try {
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
        } catch (\Exception $e) {
            Log::error('Gagal mengupdate jadwal: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengupdate jadwal karena masalah internal.'], 500);
        }
    }

    public function destroy($id)
    {
        $jadwal = Jadwal::where('id_jadwal', $id)->firstOrFail();

        try {
            $jadwal->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus jadwal: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus jadwal karena masalah internal.'], 500);
        }
    }
}
