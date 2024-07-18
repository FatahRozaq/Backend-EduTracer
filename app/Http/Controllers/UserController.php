<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ParentChild;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    
    public function searchStudentsByName(Request $request)
    {
        $request->validate([
            'nama' => 'required|string'
        ]);

        $nama = $request->nama;
        $students = User::where('roles', 'Siswa')
                        ->where('nama', 'like', '%' . $nama . '%')
                        ->get();

        return response()->json($students);
    }

    public function searchStudent(Request $request)
    {
        $keyword = $request->input('nama');
        $students = User::where('nama', 'like', "%$keyword%")
                        ->where('roles', 'student')
                        ->get();

        return response()->json($students);
    }

    public function getAllSiswa(Request $request)
    {
        // $siswa = User::where('roles', 'Siswa')->get();

        // return response()->json($siswa);
        $parent = Auth::user();

        // Mengambil ID anak yang sudah terkait dengan parent yang sedang login
        $relatedChildrenIds = $parent->childrenMany()
            ->wherePivotIn('status', ['send', 'confirm'])
            ->pluck('users.id') // Menentukan tabel secara eksplisit
            ->toArray();

        // Mengambil semua siswa yang tidak terkait dengan parent yang sedang login
        $siswa = User::where('roles', 'Siswa')
            ->whereNotIn('id', $relatedChildrenIds)
            ->get();

        return response()->json($siswa);
    }
}
