<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
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
        try {
            Log::info('Data received:', $request->all());

            $request->validate([
                'id_user' => 'required|integer',
                'id_penerima' => 'required|integer',
                'id_kelas' => 'required|integer',
                'tanggal' => 'required|date',
                'jenis_surat' => 'required|string',
                'deskripsi' => 'required|string',
                'berkas_surat' => 'nullable|file|mimes:jpg,png,jpeg,pdf|max:2048',
            ]);

            Log::info('Validation passed.');

            $suratIzin = new SuratIzin();
            $suratIzin->id_user = $request->id_user;
            $suratIzin->id_penerima = $request->id_penerima;
            $suratIzin->id_kelas = $request->id_kelas;
            $suratIzin->tanggal = $request->tanggal;
            $suratIzin->jenis_surat = $request->jenis_surat;
            $suratIzin->deskripsi = $request->deskripsi;

            if ($request->hasFile('berkas_surat')) {
                Log::info('File is present.');
                $file = $request->file('berkas_surat');
                $path = $file->store('surat_izin', 'public');
                Log::info('File stored at: ' . $path);
                $suratIzin->berkas_surat = $path;
            }

            $suratIzin->save();
            Log::info('Surat Izin saved.');

            return response()->json(['message' => 'Surat Izin berhasil dikirim'], 201);
        } catch (\Exception $e) {
            Log::error('Error storing surat izin:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Gagal mengirim Surat Izin'], 500);
        }
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
