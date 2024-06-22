<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\KelasUser;

class KelasController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'nama_kelas' => 'required|string',
            'deskripsi' => 'nullable|string',
            'enrollment_key' => 'nullable|string',
        ]);

        // Create a new instance of Kelas model and fill it with request data
        $kelas = new Kelas();
        $kelas->nama_kelas = $request->nama_kelas;
        $kelas->deskripsi = $request->deskripsi;
        $kelas->enrollment_key = $request->enrollment_key;

        // Save the new kelas
        $kelas->save();

        // Check if user is authenticated before trying to get its ID
        if (Auth::check()) {
            // Create a new instance of KelasUser model to link the new kelas with the authenticated user
            $kelasUser = new KelasUser();
            $kelasUser->id_kelas = $kelas->id_kelas; // Use the ID of the newly created kelas
            $kelasUser->id_user = Auth::id(); // Get the ID of the authenticated user

            // Save the new KelasUser entry
            $kelasUser->save();
        } else {
            // Handle the case when user is not authenticated
            // Return a response with status code 401 (Unauthorized) or perform any other actions as needed
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Return a JSON response with the newly created kelas and 201 status code
        return response()->json($kelas, 201);
    }

    public function index()
    {
        // Check if user is authenticated
        if (Auth::check()) {
            // Get the ID of the authenticated user
            $userId = Auth::id();

            // Retrieve classes associated with the authenticated user
            $kelas = Kelas::whereHas('user', function ($query) use ($userId) {
                $query->where('id_user', $userId);
            })->get();

            // Return the list of classes as a JSON response
            return response()->json($kelas);
        } else {
            // Handle the case when user is not authenticated
            // Return a response with status code 401 (Unauthorized) or perform any other actions as needed
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
