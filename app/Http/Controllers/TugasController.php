<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use Illuminate\Http\Request;

class TugasController extends Controller
{
    public function index()
    {
        // return Tugas::all();
        return response()->json(['data' => Tugas::orderBy('id_tugas', 'ASC')->get()]);
        
    }
}
