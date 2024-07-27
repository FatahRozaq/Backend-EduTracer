<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Rapot;
use App\Models\RapotLine;
use Illuminate\Support\Facades\Auth;

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

    public function getRapotLinesByRapotId($id)
    {
        // Validate if Rapot exists
        $rapot = Rapot::findOrFail($id);

        
        $rapotLines = RapotLine::where('id_rapot', $id)
            ->with('mataPelajaran:id_mata_pelajaran,nama_mapel')
            ->get();

        return response()->json([
            'message' => 'RapotLines fetched successfully',
            'rapotLines' => $rapotLines,
        ], 200);
    }

    public function getRapotId(Request $request)
    {
        $validatedData = $request->validate([
            'id_siswa' => 'required|exists:users,id',
            'jenis' => 'required|string',
            'semester' => 'required|string',
            'id_kelas' => 'required|exists:kelas,id_kelas',
        ]);

        $rapot = Rapot::where('id_siswa', $validatedData['id_siswa'])
            ->where('jenis', $validatedData['jenis'])
            ->where('semester', $validatedData['semester'])
            ->where('id_kelas', $validatedData['id_kelas'])
            ->first();

        if ($rapot) {
            return response()->json(['rapot' => $rapot], 200);
        } else {
            return response()->json(['message' => 'Rapot not found'], 404);
        }
    }


    public function checkRapot(Request $request)
    {
        $validated = $request->validate([
            'id_siswa' => 'required|exists:users,id',
            'jenis' => 'required|string',
            'semester' => 'required|string',
            'id_kelas' => 'required|exists:kelas,id_kelas',
        ]);

        $exists = Rapot::where('id_siswa', $validated['id_siswa'])
            ->where('jenis', $validated['jenis'])
            ->where('semester', $validated['semester'])
            ->where('id_kelas', $validated['id_kelas'])
            ->exists();

        return response()->json(['exists' => $exists], 200);
    }

    public function updateRapotLine(Request $request, $id_rapot_line)
    {
        // Validasi data input
        $validatedData = $request->validate([
            'nilai' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        // Temukan RapotLine berdasarkan ID
        $rapotLine = RapotLine::findOrFail($id_rapot_line);

        // Perbarui data RapotLine dengan data dari permintaan
        $rapotLine->nilai = $validatedData['nilai'];
        $rapotLine->notes = $validatedData['notes'];

        // Simpan perubahan ke database
        $rapotLine->save();

        return response()->json([
            'message' => 'RapotLine berhasil diperbarui',
            'rapotLine' => $rapotLine,
        ], 200);
    }

    public function getRapotsByLoggedInStudent()
    {
        $user = Auth::user();

        if ($user->roles !== 'Siswa') {
            return response()->json(['message' => 'Only students can access this resource.'], 403);
        }

        $rapots = Rapot::where('id_siswa', $user->id)
            ->with('kelas')
            ->with('siswa')
            ->get();

        return response()->json([
            'message' => 'Rapots fetched successfully',
            'rapots' => $rapots,
        ], 200);
    }




}
