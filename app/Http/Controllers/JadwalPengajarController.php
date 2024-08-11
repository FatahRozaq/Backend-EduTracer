<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Jadwal;

class JadwalPengajarController extends Controller
{
    public function getJadwalPengajar()
    {
        $userId = Auth::id();

        $jadwals = Jadwal::with(['kelas', 'mataPelajaran'])
            ->whereHas('pengajarMapel', function ($query) use ($userId) {
                $query->where('id_user', $userId);
            })
            ->get();

        return response()->json($jadwals);
    }
}
