<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Mail;

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

    public function sendEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'nama'  => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->email;
        // $nama = $request->nama;

        // Cek rate limit: 1x per 30 detik (optional)
        if (Cache::has("otp_email_block:$email")) {
            return response()->json([
                'message' => 'Silakan tunggu beberapa saat sebelum mengirim ulang OTP.',
            ], 429);
        }

        // Generate OTP 6 digit
        $otp = rand(100000, 999999);

        // Simpan OTP ke cache selama 5 menit
        Cache::put("otp_email:$email", $otp, now()->addMinutes(5));

        // Blokir permintaan OTP selama 30 detik
        Cache::put("otp_email_block:$email", true, now()->addSeconds(30));

        // Kirim email menggunakan Mail
        try {
            Mail::to($email)->send(new OtpMail($otp));
            // Mail::to($email)->send(new OtpMail($otp, $nama));

            return response()->json([
                'message' => 'OTP berhasil dikirim ke email Anda.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengirim OTP. Silakan coba lagi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->email;
        $otpInput = $request->otp;

        // Ambil OTP yang tersimpan di cache
        $cachedOtp = Cache::get("otp_email:$email");

        if (!$cachedOtp) {
            return response()->json([
                'message' => 'Kode OTP sudah kedaluwarsa atau belum dikirim.',
            ], 410); // 410 Gone
        }

        if ($otpInput != $cachedOtp) {
            return response()->json([
                'message' => 'Kode OTP tidak sesuai.',
            ], 400);
        }

        // Hapus OTP setelah berhasil diverifikasi (opsional)
        Cache::forget("otp_email:$email");

        return response()->json([
            'message' => 'OTP email berhasil diverifikasi.',
        ]);
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
