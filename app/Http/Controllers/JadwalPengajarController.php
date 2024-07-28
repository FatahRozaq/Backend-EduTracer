<?php

// JadwalPengajarController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\JadwalPengajar;
use App\Models\Jadwal;

class JadwalPengajarController extends Controller
{
    public function getJadwalPengajar()
    {
        $userId = Auth::id();

        $jadwalsPengajar = JadwalPengajar::where('id_user', $userId)
            ->with('jadwal.kelas', 'jadwal.mataPelajaran')
            ->get();

        $jadwals = $jadwalsPengajar->map(function($jp) {
            return $jp->jadwal;
        });

        return response()->json($jadwals);
    }
}
