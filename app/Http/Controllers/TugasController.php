<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TugasController extends Controller
{
    public function index()
    {
        try {
            $tugas = Tugas::orderBy('id_tugas', 'ASC')->get();
            return response()->json([
                'success' => true,
                'data' => $tugas,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $tugas = Tugas::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $tugas,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nama_tugas' => 'required',
                'deskripsi' => 'required',
                'status' => 'required',
                'tenggat_tugas' => 'required|date',
            ]);

            $tugas = Tugas::create($validatedData);
            return response()->json([
                'success' => true,
                'data' => $tugas,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $tugas = Tugas::findOrFail($id);

            $validatedData = $request->validate([
                'nama_tugas' => 'required',
                'deskripsi' => 'required',
                'tenggat_tugas' => 'required|date',
                'status' => 'required',
            ]);

            $tugas->update($validatedData);
            return response()->json([
                'success' => true,
                'data' => $tugas,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas not found',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $tugas = Tugas::findOrFail($id);
            $tugas->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
            ], 204);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }
}
