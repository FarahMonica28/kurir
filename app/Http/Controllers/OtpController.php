<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Models\EmailVerification;

class OtpController extends Controller
{
    // Kirim OTP berdasarkan user_id
    // public function send(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //     ]);

    //     $user = User::findOrFail($request->user_id);
    //     $email = $user->email;

    //     $otp = rand(100000, 999999); // kode 6 digit
    //     $expiresAt = now()->addMinutes(5);

    //     EmailVerification::updateOrCreate(
    //         ['email' => $email],
    //         ['otp' => $otp, 'expires_at' => $expiresAt]
    //     );

    //     // Kirim email
    //     Mail::raw("Kode OTP Anda adalah: $otp", function ($message) use ($email) {
    //         $message->to($email)
    //                 ->subject("Kode OTP Verifikasi");
    //     });

    //     return response()->json([
    //         'message' => 'OTP berhasil dikirim ke email',
    //     ]);
    // }

    // // Verifikasi OTP berdasarkan user_id + otp
    // public function verify(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'otp' => 'required|string',
    //     ]);

    //     $user = User::findOrFail($request->user_id);
    //     $email = $user->email;

    //     $verification = EmailVerification::where('email', $email)->first();

    //     if (!$verification) {
    //         return response()->json(['message' => 'OTP tidak ditemukan'], 404);
    //     }

    //     if ($verification->otp !== $request->otp) {
    //         return response()->json(['message' => 'OTP salah'], 422);
    //     }

    //     if (Carbon::now()->gt($verification->expires_at)) {
    //         return response()->json(['message' => 'OTP sudah kedaluwarsa'], 422);
    //     }

    //     // Update user → verified
    //     if (!$user->email_verified_at) {
    //         $user->email_verified_at = now();
    //         $user->save();
    //     }

    //     return response()->json(['message' => 'OTP berhasil diverifikasi']);
    // }


    public function send($uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        $otp = rand(100000, 999999);

        // Simpan ke cache selama 5 menit
        Cache::put("otp_{$user->id}", $otp, now()->addMinutes(5));

        // Kirim ke email
        Mail::to($user->email)->send(new \App\Mail\OtpMail($otp));

        return response()->json(['message' => 'OTP berhasil dikirim']);
    }

    public function verify(Request $request, $uuid)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        $user = User::where('uuid', $uuid)->firstOrFail();
        $cacheKey = "otp_{$user->id}";
        $otp = Cache::get($cacheKey);

        if ($otp && $otp == $request->otp) {
            $user->email_verified_at = Carbon::now();
            $user->save();

            Cache::forget($cacheKey);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'OTP salah atau kadaluarsa'], 422);
    }

//     public function verify(Request $request, $uuid)
// {
//     $user = User::where('uuid', $uuid)->firstOrFail();

//     if ($request->otp == $user->otp_code) {
//         $user->email_verified_at = now();
//         $user->otp_code = null;
//         $user->save();

//         return response()->json(['success' => true]);
//     }

//     return response()->json(['success' => false]);
// }
}
