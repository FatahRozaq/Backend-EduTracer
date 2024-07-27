<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Rapot;
use App\Models\RapotLine;

class RapotController extends Controller
{
    /**
     * Create a new Rapot.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createRapot(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'id_siswa' => 'required|exists:users,id',
            'jenis' => 'required|string',
            'semester' => 'required|string',
            'id_kelas' => 'required|exists:kelas,id_kelas',
        ]);

        // Check if a Rapot with the same criteria already exists
        $existingRapot = Rapot::where('id_siswa', $validated['id_siswa'])
            ->where('jenis', $validated['jenis'])
            ->where('semester', $validated['semester'])
            ->where('id_kelas', $validated['id_kelas'])
            ->first();

        if ($existingRapot) {
            return response()->json([
                'message' => 'Rapot already exists',
            ], 409); // 409 Conflict status code
        }

        // Create the Rapot
        $rapot = new Rapot();
        $rapot->id_siswa = $validated['id_siswa'];
        $rapot->jenis = $validated['jenis'];
        $rapot->semester = $validated['semester'];
        $rapot->id_kelas = $validated['id_kelas'];
        $rapot->save();

        // Fetch MataPelajaran associated with the class
        $mataPelajaran = Kelas::find($validated['id_kelas'])->mataPelajaran;

        // Create RapotLines for each MataPelajaran
        foreach ($mataPelajaran as $mapel) {
            $rapotLine = new RapotLine();
            $rapotLine->id_rapot = $rapot->id;
            $rapotLine->id_mapel = $mapel->id_mata_pelajaran;
            $rapotLine->nilai = 0; // Set a default value for 'nilai'
            $rapotLine->notes = ''; // Set a default value for 'notes'
            $rapotLine->save();
        }

        return response()->json([
            'message' => 'Rapot and RapotLines created successfully',
            'rapot' => $rapot,
        ], 201);
    }

    /**
     * Create a new RapotLine.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createRapotLine(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'id_rapot' => 'required|exists:rapot,id',
            'id_mapel' => 'required|exists:mata_pelajaran,id_mata_pelajaran',
            'nilai' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        // Create the RapotLine
        $rapotLine = new RapotLine();
        $rapotLine->id_rapot = $validated['id_rapot'];
        $rapotLine->id_mapel = $validated['id_mapel'];
        $rapotLine->nilai = $validated['nilai'];
        $rapotLine->notes = $validated['notes'];
        $rapotLine->save();

        return response()->json([
            'message' => 'RapotLine created successfully',
            'rapotLine' => $rapotLine,
        ], 201);
    }
}
