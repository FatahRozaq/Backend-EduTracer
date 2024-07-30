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
        // $idKelas = $request['id_kelas'];
        // $idMapel = $request['id_mata_pelajaran'];
        // $idKelasMapel = $request['id_kelas_mata_pelajaran'];
        try {
            $validatedData = $request->validate([
                'nama_tugas' => 'required',
                'deskripsi' => 'required',
                'status' => 'required',
                'tenggat_tugas' => 'required',
                'id_kelas_mata_pelajaran' => 'required',
                'file_tugas' => 'nullable|file|max:4096'
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
                'id_kelas_mata_pelajaran' => 'required',
                'file_tugas' => 'nullable|file|max:4096'
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



    // Kelompok Tugas

    public function getTugasDetail($id_tugas)
    {
        try {
            $tugas = Tugas::findOrFail($id_tugas);

            return response()->json([
                'success' => true,
                'data' => $tugas,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }
    }


    public function storeKelompokTugas(Request $request)
    {
        try {
            // Validate the single input fields
            $validatedData = $request->validate([
                'nama_tugas' => 'required',
                'deskripsi' => 'required',
                'status' => 'required',
            ]);

            // Validate the multiple input fields
            $multipleFields = $request->validate([
                'tenggat_tugas' => 'required|array',
                'tenggat_tugas.*' => 'required|date',
                'id_kelas_mata_pelajaran' => 'required|array',
                'id_kelas_mata_pelajaran.*' => 'required'
            ]);

            // Arrays to store multiple entries
            $tenggatTugasArray = $multipleFields['tenggat_tugas'];
            $idKelasMataPelajaranArray = $multipleFields['id_kelas_mata_pelajaran'];

            $tugasEntries = [];

            // Loop through multiple input fields and create entries
            foreach ($tenggatTugasArray as $index => $tenggatTugas) {
                $tugasEntries[] = [
                    'nama_tugas' => $validatedData['nama_tugas'],
                    'deskripsi' => $validatedData['deskripsi'],
                    'status' => $validatedData['status'],
                    'tenggat_tugas' => $tenggatTugas,
                    'id_kelas_mata_pelajaran' => $idKelasMataPelajaranArray[$index],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Insert multiple entries into the database
            Tugas::insert($tugasEntries);

            return response()->json([
                'success' => true,
                'data' => $tugasEntries,
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

    public function storeAdditionalData(Request $request, $id_tugas)
    {
        try {
            // Retrieve the task details by id
            $tugas = Tugas::findOrFail($id_tugas);

            // Validate the multiple input fields
            $multipleFields = $request->validate([
                'tenggat_tugas' => 'required|array',
                'tenggat_tugas.*' => 'required|date',
                'id_kelas_mata_pelajaran' => 'required|array',
                'id_kelas_mata_pelajaran.*' => 'required'
            ]);

            // Arrays to store multiple entries
            $tenggatTugasArray = $multipleFields['tenggat_tugas'];
            $idKelasMataPelajaranArray = $multipleFields['id_kelas_mata_pelajaran'];

            $tugasEntries = [];

            // Loop through multiple input fields and create entries
            foreach ($tenggatTugasArray as $index => $tenggatTugas) {
                $tugasEntries[] = [
                    'nama_tugas' => $tugas->nama_tugas,
                    'deskripsi' => $tugas->deskripsi,
                    'status' => $tugas->status,
                    'tenggat_tugas' => $tenggatTugas,
                    'id_kelas_mata_pelajaran' => $idKelasMataPelajaranArray[$index],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Insert multiple entries into the database
            Tugas::insert($tugasEntries);

            return response()->json([
                'success' => true,
                'data' => $tugasEntries,
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

    public function editTugasByNama(Request $request, $nama_tugas)
    {
        try {
            // Validate the input fields
            $validatedData = $request->validate([
                'deskripsi' => 'sometimes|required',
                'status' => 'sometimes|required',
                'tenggat_tugas' => 'required|array',
                'tenggat_tugas.*' => 'required|date',
                'id_kelas_mata_pelajaran' => 'required|array',
                'id_kelas_mata_pelajaran.*' => 'required'
            ]);

            // Retrieve the tasks by nama_tugas
            $tasks = Tugas::where('nama_tugas', $nama_tugas)->get();

            if ($tasks->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found',
                ], 404);
            }

            // Update each task entry based on id_kelas_mata_pelajaran
            foreach ($tasks as $task) {
                $index = array_search($task->id_kelas_mata_pelajaran, $validatedData['id_kelas_mata_pelajaran']);
                if ($index !== false) {
                    // Update the task entry with new data
                    $task->update([
                        'deskripsi' => $request->input('deskripsi', $task->deskripsi),
                        'status' => $request->input('status', $task->status),
                        'tenggat_tugas' => $validatedData['tenggat_tugas'][$index],
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $tasks,
            ], 200);
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

    public function deleteTugasByNama(Request $request, $nama_tugas)
    {
        try {
            // Validate the input fields
            $validatedData = $request->validate([
                'id_kelas_mata_pelajaran' => 'required|array',
                'id_kelas_mata_pelajaran.*' => 'required'
            ]);

            // Retrieve the tasks by nama_tugas and id_kelas_mata_pelajaran
            $tasks = Tugas::where('nama_tugas', $nama_tugas)
                        ->whereIn('id_kelas_mata_pelajaran', $validatedData['id_kelas_mata_pelajaran'])
                        ->get();

            if ($tasks->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tasks found for the given criteria',
                ], 404);
            }

            // Delete the tasks
            foreach ($tasks as $task) {
                $task->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Tasks deleted successfully',
                'data' => $tasks,
            ], 200);
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





    // public function store(Request $request)
    // {
    //     $idKelas = $request['id_kelas'];
    //     // $idMapel = $request['id_mata_pelajaran'];
    //     try {
    //         $validatedData = $request->validate([
    //             'nama_tugas' => 'required',
    //             'deskripsi' => 'required',
    //             'status' => 'required',
    //             'tenggat_tugas' => 'required|date',
    //             // 'id_kelas_mata_pelajaran' => 'required'
    //         ]);

    //         $tugas = Tugas::create($validatedData);
    //         return response()->json([
    //             'success' => true,
    //             'data' => $tugas,
    //         ], 201);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->errors(),
    //         ], 422);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred',
    //         ], 500);
    //     }
    // }

    

    public function getTugasByKelasMataPelajaran($id_kelas_mata_pelajaran)
    {
        try {
            $tugas = Tugas::where('id_kelas_mata_pelajaran', $id_kelas_mata_pelajaran)->get();
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
}
