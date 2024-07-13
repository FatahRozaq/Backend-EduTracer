<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $req)
    {
        //valdiate
        $rules = [
            'nama' => 'required|string',
            'email' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
            'no_tlp' => 'string',
            'alamat' => 'string',
            'roles' => 'required|string',
            'partner_id' => 'integer'
        ];
        $validator = Validator::make($req->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        
        $user = User::create([
            'nama' => $req->nama,
            'email' => $req->email,
            'password' => Hash::make($req->password),
            'no_tlp' => $req->no_tlp,
            'alamat' => $req->alamat,
            'roles' => $req->roles,
            'partner_id' => $req->partner_id,
        ]);
        $token = $user->createToken('Personal Access Token')->plainTextToken;
        $response = ['user' => $user, 'token' => $token];
        return response()->json($response, 200);
    }

    public function login(Request $req)
    {
        
        $rules = [
            'email' => 'required',
            'password' => 'required|string'
        ];
        $req->validate($rules);
        
        $user = User::where('email', $req->email)->first();
        
        if ($user && Hash::check($req->password, $user->password)) {
            $token = $user->createToken('Personal Access Token')->plainTextToken;
            $response = ['user' => $user, 'token' => $token];
            return response()->json($response, 200);
        }
        $response = ['message' => 'Incorrect email or password'];
        return response()->json($response, 400);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function getUser(Request $request)
    {
        return response()->json($request->user());
    }
}
