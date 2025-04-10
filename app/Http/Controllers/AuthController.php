<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function me()
    {
        $user = Auth::user()->load('kurir'); // penting: load relasi kurir
        return response()->json([
            'user' => auth()->user()
        ]);
    }

    public function login(Request $request)
    {
        
        $validator = Validator::make($request->post(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json([
                'status' => false,
                'message' => 'Email / Password salah!'
            ], 401);
        }
        // $user = Auth::user()->load('kurir'); // <- ini WAJIB

        return response()->json([
            'status' => true,
            'user' => auth()->user()->load('kurir'),
            'token' => $token
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['success' => true]);
    }
}
