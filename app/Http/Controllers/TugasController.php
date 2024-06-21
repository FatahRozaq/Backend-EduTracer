<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use Illuminate\Http\Request;

class TugasController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Tugas::orderBy('id_tugas', 'ASC')->get()]);
    }

    public function show($id)
    {
        return Tugas::findOrFail($id);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_tugas' => 'required',
            'deskripsi' => 'required',
            'status' => 'required',
        ]);

        $tugas = Tugas::create($validatedData);

        return response()->json($tugas, 201);
    }

    public function update(Request $request, $id)
    {
        $tugas = Tugas::findOrFail($id);

        $validatedData = $request->validate([
            'nama_tugas' => 'required',
            'deskripsi' => 'required',
            'tenggat_tugas' => 'required|date',
            'status' => 'required',
        ]);

        $tugas->update($validatedData);

        return response()->json($tugas, 200);
    }

    public function destroy($id)
    {
        $tugas = Tugas::findOrFail($id);
        $tugas->delete();

        return response()->json("Data berhasil dihapus", 204);
    }
}
