<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TugasKelasMataPelajaran;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TugasSiswaController extends Controller
{
    public function index()
    {
        try {
            $data = TugasKelasMataPelajaran::all();
            return response()->json([
                'success' => true,
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
