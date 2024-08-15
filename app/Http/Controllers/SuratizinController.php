<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\SuratIzin;
use Illuminate\Support\Facades\Storage;

class SuratIzinController extends Controller
{
    public function indexOrtu()
    {
        $userId = auth()->user()->id;
        $suratIzins = SuratIzin::with(['pengirim', 'anak', 'penerima'])
            ->where('id_user', $userId)
            ->get();
        return response()->json($suratIzins);
    }

    public function indexGuru()
    {
        $userId = auth()->user()->id;
        $suratIzins = SuratIzin::with(['pengirim', 'anak', 'penerima'])
            ->where('id_penerima', $userId)
            ->get();
        return response()->json($suratIzins);
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'id_user' => 'required|integer',
            'id_penerima' => 'required|integer',
            'id_kelas' => 'required|integer',
            'id_anak' => 'required|integer',
            'tanggal' => 'required|date',
            'jenis_surat' => 'required|string',
            'deskripsi' => 'required|string',
            'berkas_surat' => 'nullable|file|mimes:jpg,png,jpeg,pdf|max:4096',
        ]);

        // Buat instance model SuratIzin
        $suratIzin = new SuratIzin();
        $suratIzin->id_user = $request->id_user;
        $suratIzin->id_penerima = $request->id_penerima;
        $suratIzin->id_kelas = $request->id_kelas;
        $suratIzin->id_anak = $request->id_anak;
        $suratIzin->tanggal = $request->tanggal;
        $suratIzin->jenis_surat = $request->jenis_surat;
        $suratIzin->deskripsi = $request->deskripsi;

        if ($request->hasFile('berkas_surat')) {
            $file = $request->file('berkas_surat');
            $path = $file->store('surat_izin', 'public');
            $suratIzin->berkas_surat = $path;
        }

        $suratIzin->save();

        return response()->json(['message' => 'Surat Izin berhasil dikirim'], 201);
    }


    public function show($id)
    {
        $suratIzin = SuratIzin::with(['pengirim', 'anak', 'penerima'])->findOrFail($id);
        if (!$suratIzin->read_status) {
            $suratIzin->read_status = true;
            $suratIzin->save();
        }

        return response()->json($suratIzin);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_user' => 'required|exists:users,id',
            'id_penerima' => 'required|exists:users,id',
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_anak' => 'required|exists:users,id',
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

    public function markAsRead($id)
    {
        $suratIzin = SuratIzin::findOrFail($id);
        $suratIzin->read_status = true;
        $suratIzin->save();

        return response()->json(['message' => 'Surat Izin telah dibaca']);
    }
}
