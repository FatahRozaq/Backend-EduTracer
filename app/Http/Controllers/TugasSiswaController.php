<?php

namespace App\Http\Controllers;

use App\Models\KelasMataPelajaran;
use App\Models\Tugas;
use Illuminate\Http\Request;
use App\Models\TugasKelasMataPelajaran;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TugasSiswaController extends Controller
{
    public function index($idKelas)
{
    try {
        // $idKelas = $request->input('id_kelas');

        // Mengambil id_kelas_mata_pelajaran berdasarkan id_kelas
        $kelas = KelasMataPelajaran::where('id_kelas', $idKelas)->pluck('id_kelas_mata_pelajaran');
        
        // Mengambil tugas dan melakukan eager loading untuk relasi kelas dan mata pelajaran
        $tugas = Tugas::with(['kelasMataPelajaran.kelas', 'kelasMataPelajaran.mataPelajaran'])
            ->whereIn('id_kelas_mata_pelajaran', $kelas)
            ->get();
        
        // Membentuk data response dengan tugas, nama kelas, dan nama mata pelajaran
        $data = $tugas->map(function ($item) {
            return [
                'id_tugas' => $item->id_tugas,
                'nama_tugas' => $item->nama_tugas,
                'deskripsi' => $item->deskripsi,
                'tenggat_tugas' => $item->tenggat_tugas,
                'status' => $item->status,
                'file' => $item->file,
                'file_path' => $item->file_path,
                'nama_kelas' => $item->kelasMataPelajaran->kelas->nama_kelas ?? null,
                'nama_mapel' => $item->kelasMataPelajaran->mataPelajaran->nama_mapel ?? null,
            ];
        });

        $number = 10;

        return response()->json([
            'success' => true,
            'message' => 'Data tugas berhasil diambil',
            'data' => $data
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve data',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function showById($idTugas)
{
    try {
        // Mengambil tugas berdasarkan id_tugas dengan eager loading untuk relasi kelas dan mata pelajaran
        $tugas = Tugas::with(['kelasMataPelajaran.kelas', 'kelasMataPelajaran.mataPelajaran'])
            ->findOrFail($idTugas);
        
        // Membentuk data response dengan tugas, nama kelas, dan nama mata pelajaran
        $data = [
            'id_tugas' => $tugas->id_tugas,
            'nama_tugas' => $tugas->nama_tugas,
            'deskripsi' => $tugas->deskripsi,
            'tenggat_tugas' => $tugas->tenggat_tugas,
            'status' => $tugas->status,
            'file' => $tugas->file,
            'file_path' => $tugas->file_path,
            'id_kelas_mata_pelajaran' => $tugas->id_kelas_mata_pelajaran,
            'nama_kelas' => $tugas->kelasMataPelajaran->kelas->nama_kelas ?? null,
            'nama_mapel' => $tugas->kelasMataPelajaran->mataPelajaran->nama_mapel ?? null,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detail tugas berhasil diambil',
            'data' => $data
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve data',
            'error' => $e->getMessage()
        ], 500);
    }
}





    // Show data by ID
    public function show($id)
    {
        try {
            $data = TugasKelasMataPelajaran::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_tugas' => 'required|integer',
            'id_kelas_mata_pelajaran' => 'required|integer',
            'id_user' => 'required|integer',
            'status' => 'required|string',
            'berkas' => 'nullable|file|max:4096', // 4MB in kilobytes
            'nilai_tugas' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $data = $request->all();

            if ($request->hasFile('berkas')) {
                $file = $request->file('berkas');
                $filePath = $file->store('public/tugas_files'); // Store the file in the 'public/tugas_files' directory
                $data['berkas'] = $filePath;
            }

            $tugasKelasMataPelajaran = TugasKelasMataPelajaran::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Data successfully saved',
                'data' => $tugasKelasMataPelajaran
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data failed to save',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_tugas' => 'required|integer',
            'id_kelas_mata_pelajaran' => 'required|integer',
            'id_user' => 'required|integer',
            'status' => 'required|string',
            'berkas' => 'nullable|file|max:4096', // 4MB in kilobytes
            'nilai_tugas' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $tugasKelasMataPelajaran = TugasKelasMataPelajaran::findOrFail($id);

            $data = $request->all();

            if ($request->hasFile('berkas')) {
                // Delete the old file if it exists
                if ($tugasKelasMataPelajaran->berkas) {
                    Storage::delete($tugasKelasMataPelajaran->berkas);
                }

                $file = $request->file('berkas');
                $filePath = $file->store('public/tugas_files'); // Store the file in the 'public/tugas_files' directory
                $data['berkas'] = $filePath;
            }

            $tugasKelasMataPelajaran->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data successfully updated',
                'data' => $tugasKelasMataPelajaran
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data failed to update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete data
    public function destroy($id)
    {
        try {
            $tugasKelasMataPelajaran = TugasKelasMataPelajaran::findOrFail($id);

            // Delete the file if it exists
            if ($tugasKelasMataPelajaran->berkas) {
                Storage::delete($tugasKelasMataPelajaran->berkas);
            }

            $tugasKelasMataPelajaran->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data successfully deleted'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data failed to delete',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateNilaiTugas(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nilai_tugas' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $tugasKelasMataPelajaran = TugasKelasMataPelajaran::findOrFail($id);
            $tugasKelasMataPelajaran->nilai_tugas = $request->input('nilai_tugas');
            $tugasKelasMataPelajaran->save();

            return response()->json([
                'success' => true,
                'message' => 'Nilai tugas successfully updated',
                'data' => $tugasKelasMataPelajaran
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update nilai tugas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
