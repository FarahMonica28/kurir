<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function me()
    {
        $user = auth()->user()->load('kurir', 'pengguna');
        return response()->json([
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Ambil user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Cek apakah user ada
        if (!$user || !\Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Email / Password salah!'
            ], 401);
        }

        // Cek apakah user sudah verifikasi email
        if (is_null($user->email_verified_at)) {
            return response()->json([
                'status' => false,
                'message' => 'Email belum diverifikasi.'
            ], 403);
        }

        // Pastikan User implement JWTSubject
        if (!($user instanceof \PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject)) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak bisa login melalui JWT.'
            ], 500);
        }

        // Login dan ambil token
        $token = auth()->login($user);

        return response()->json([
            'status' => true,
            'user' => $user->load('kurir'),
            'token' => $token
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['success' => true]);
    }
}

//     public function verifyEmailOtp(Request $request)
// {
//     $request->validate([
//         'email' => 'required|email',
//         'otp' => 'required',
//     ]);

//     $user = User::where('email', $request->email)->firstOrFail();

//     $otpEntry = EmailVerification::where('user_id', $user->id)
//         ->where('otp', $request->otp)
//         ->latest()
//         ->first();

//     if (!$otpEntry || $otpEntry->isExpired()) {
//         return response()->json(['message' => 'OTP tidak valid atau sudah kadaluarsa'], 422);
//     }

//         $user->email_verified_at = now();
//         $user->otp = null; // hapus OTP setelah berhasil diverifikasi
//         $user->save();

//         return response()->json(['message' => 'Email berhasil diverifikasi']);

//     // Hapus semua OTP setelah verifikasi
//     // EmailVerification::where('user_id', $user->id)->delete();

// }
// }
