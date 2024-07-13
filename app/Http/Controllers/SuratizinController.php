<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratIzin;

class SuratIzinController extends Controller
{
    public function index()
    {
        $suratIzins = SuratIzin::all();
        return response()->json($suratIzins);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|exists:users,id',
            'id_penerima' => 'required|exists:users,id',
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'tanggal' => 'required|date',
            'jenis_surat' => 'required|string|max:5',
            'deskripsi' => 'required|string|max:255',
            'berkas_surat' => 'nullable|string|max:255',
        ]);

        $suratIzin = SuratIzin::create($validated);

        return response()->json($suratIzin, 201);
    }

    public function show($id)
    {
        $suratIzin = SuratIzin::findOrFail($id);
        return response()->json($suratIzin);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_user' => 'required|exists:users,id',
            'id_penerima' => 'required|exists:users,id',
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'tanggal' => 'required|date',
            'jenis_surat' => 'required|string|max:5',
            'deskripsi' => 'required|string|max:255',
            'berkas_surat' => 'nullable|string|max:255',
        ]);

        $suratIzin = SuratIzin::findOrFail($id);
        $suratIzin->update($validated);

        return response()->json($suratIzin);
    }

    public function destroy($id)
    {
        $suratIzin = SuratIzin::findOrFail($id);
        $suratIzin->delete();

        return response()->json(null, 204);
    }
}
