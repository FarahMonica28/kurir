<?php

namespace App\Http\Controllers;

use App\Models\Transaksii;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class TrackingController extends Controller
{
    /**
     * Melacak status pengiriman berdasarkan no_resi.
     */

    // public function tracking($no_resi)
    // {
    //     // $transaksii = Transaksii::where('no_resi', $no_resi)->with(['pengguna', 'kurir'])->with(['asalKota','tujuanKota','tujuanProvinsi','asalProvinsi'])->first();
    // $transaksii = Transaksii::with('pengiriman')->where('no_resi', $no_resi)->with(['asalKota','tujuanKota','tujuanProvinsi','asalProvinsi', 'asalKecamatan', 'tujuanKecamatan', 'kurir', 'pengguna'])->first();
    //     // $transaksii = Transaksii::where('no_resi', $no_resi)->with(['asalKota','tujuanKota','tujuanProvinsi','asalProvinsi', 'kurir', 'pengguna'])->first();
    
    //     Log::info("info : ", ["info" => $transaksii]);

    //     if (!$transaksii) {
    //         return response()->json([
    //             'message' => 'Transaksi dengan nomor resi tersebut tidak ditemukan.'
    //         ], 404);
    //     }

    //     if (!$transaksii->pengiriman) {
    //         return response()->json(['message' => 'Data pengiriman belum tersedia'], 404);
    //     }
    //     $ambilPengiriman = $transaksii->pengiriman->firstWhere('status', 'ambil');
    //     $antarPengiriman = $transaksii->pengiriman->firstWhere('status', 'antar');
    //         // Log::info("INFO",["Ambil" => $ambilPengiriman->kurir]);
            
    //         // Ambil user dari kurir masing-masing
    //         $transaksii->ambil = $ambilPengiriman?->kurir->user;
    //         $transaksii->antar = $antarPengiriman?->kurir->user;

    //     return response()->json([
    //         'message' => 'Data transaksi ditemukan.',
    //         'data' => $transaksii
    //     ]);
    // }


    public function tracking(Request $request, $resi)
        {
            // $transaksi = Transaksii::where('no_resi', $resi)->first();
            $transaksii = Transaksii::with('pengiriman')->where('no_resi', $resi)->with(['asalKota','tujuanKota','tujuanProvinsi','asalProvinsi', 'asalKecamatan', 'tujuanKecamatan', 'kurir', 'pengguna'])->first();

            if (!$transaksii) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Resi tidak ditemukan'
                ], 404);
            }
            if (!$transaksii) {
            return response()->json([
                'message' => 'Transaksi dengan nomor resi tersebut tidak ditemukan.'
            ], 404);
            }

            if (!$transaksii->pengiriman) {
                return response()->json(['message' => 'Data pengiriman belum tersedia'], 404);
            }
            $ambilPengiriman = $transaksii->pengiriman->firstWhere('status', 'ambil');
            $antarPengiriman = $transaksii->pengiriman->firstWhere('status', 'antar');
            // Log::info("INFO",["Ambil" => $ambilPengiriman->kurir]);
            
            // Ambil user dari kurir masing-masing
            $transaksii->ambil = $ambilPengiriman?->kurir->user;
            $transaksii->antar = $antarPengiriman?->kurir->user;


            // Kalau request ada parameter last4 → cek verifikasi nomor HP
            if ($request->has('last4')) {
                // Misal nomor ada di kolom `no_hp_pengirim` dan `no_hp_penerima`
                $phonePengirim = preg_replace('/\D/', '', $transaksii->no_hp_pengirim ?? '');
                $phonePenerima = preg_replace('/\D/', '', $transaksii->no_hp_penerima ?? '');

                $last4Pengirim = substr($phonePengirim, -4);
                $last4Penerima = substr($phonePenerima, -4);

                if ($request->last4 === $last4Pengirim || $request->last4 === $last4Penerima) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Verifikasi berhasil',
                        'data' => $transaksii // bisa ditambahkan relasi kurir, tracking, dll
                    ]);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Nomor HP tidak sesuai'
                    // 'message' => 'Verifikasi gagal'
                ], 422);
            }

            // Kalau belum ada last4 → berarti minta verifikasi dulu
            return response()->json([
                'status' => 'need_verification',
                'message' => 'Masukkan 4 digit terakhir nomor HP pengirim/penerima untuk resi ' . $resi
            ]);
        }



        
//     public function track(Request $request, $noResi)
// {
//     $request->validate([
//         'last4' => 'required|digits:4'
//     ]);

//     $transaksi = Transaksii::where('no_resi', $noResi)->first();

//     if (!$transaksi) {
//         return response()->json([
//             'message' => 'Nomor resi tidak ditemukan.'
//         ], 404);
//     }

//     // Ambil 4 digit terakhir nomor HP penerima
//     $hpPenerima = preg_replace('/\D/', '', $transaksi->hp_penerima); // buang selain angka
//     $last4FromDb = substr($hpPenerima, -4);

//     if ($last4FromDb !== $request->last4) {
//         return response()->json([
//             'message' => '4 digit nomor HP penerima tidak cocok.'
//         ], 403);
//     }

//     return response()->json([
//         'message' => 'Berhasil mengambil data tracking',
//         'data' => $transaksi
//     ]);
// }

    // public function track(Request $request, $noResi)
    // {
    //     // Validasi input
    //     $validated = $request->validate([
    //         'last4' => ['required', 'digits:4'],
    //     ]);

    //     // Cari transaksi berdasarkan nomor resi
    //     $transaksii = Transaksii::where('no_resi', $noResi)->first();

    //     // Kalau nomor resi tidak ditemukan
    //     if (!$transaksii->) {
    //         return response()->json([
    //             'status' => 'error',
    //             'field'  => 'no_resi',
    //             'message' => 'Nomor resi tidak ditemukan.'
    //         ], 404);
    //     }

    //     // Pastikan nomor HP pengirim tersedia
    //     if (!$transaksii->no_hp_pengirim) {
    //         return response()->json([
    //             'status' => 'error',
    //             'field'  => 'no_hp_pengirim',
    //             'message' => 'Nomor HP pengirim tidak tersedia.'
    //         ], 400);
    //     }

    //     // Ambil 4 digit terakhir nomor HP pengirim
    //     $hpPengirim   = preg_replace('/\D/', '', $transaksii->no_hp_pengirim);
    //     $last4FromDb  = substr($hpPengirim, -4);

    //     // Kalau 4 digit terakhir tidak cocok
    //     if ($last4FromDb !== $validated['last4']) {
    //         return response()->json([
    //             'status' => 'error',
    //             'field'  => 'last4',
    //             'message' => '4 digit nomor HP pengirim salah.'
    //         ], 403);
    //     }

    //     // Kalau lolos semua validasi
    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Berhasil mengambil data tracking',
    //         'data' => $transaksii
    //     ]);
    // }

//     public function track(Request $request, $noResi)
// {
//     $validated = $request->validate([
//         'last4' => ['required', 'digits:4'],
//     ]);

//     $transaksii = Transaksii::where('no_resi', $noResi)->first();

//     if (!$transaksii) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Nomor resi tidak ditemukan.'
//         ], 404);
//     }

//     // cek nomor hp pengirim
//     if (!$transaksii->no_hp_pengirim) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Nomor HP pengirim tidak tersedia.'
//         ], 400);
//     }

//     $hpPenerima = preg_replace('/\D/', '', $transaksii->no_hp_pengirim);
//     $last4FromDb = substr($hpPenerima, -4);

//     if ($last4FromDb !== $validated['last4']) {
//         return response()->json([
//             'status' => 'error',
//             'message' => '4 digit nomor HP Pengirim salah.'
//         ], 403);
//     }

//     return response()->json([
//         'status' => 'success',
//         'message' => 'Berhasil mengambil data tracking',
//         'data' => $transaksii
//     ]);
// }

    public function findByPhone(Request $request)
{
    $last4 = $request->query('last4');

    if (!$last4) {
        return response()->json(['message' => '4 digit terakhir HP wajib diisi'], 400);
    }

    $transaksi = Transaksii::whereHas('penerima', function ($q) use ($last4) {
        $q->where('phone', 'like', "%$last4");
    })->with(['pengirim','penerima','pengiriman.kurir'])->first();

    if (!$transaksi) {
        return response()->json(['message' => 'Data tidak ditemukan'], 404);
    }

    return response()->json(['data' => $transaksi]);
}


    // TrackingController.php
public function show(Request $request, $resi)
{
    $last4 = $request->query('last4'); // ambil 4 digit terakhir dari query string

    // Ambil data transaksi berdasarkan resi
    $transaksii = Transaksii::with(['pengirim', 'penerima'])->where('no_resi', $resi)->first();

    if (!$transaksii) {
        return response()->json(['message' => 'Nomor resi tidak ditemukan'], 404);
    }

    // Pastikan last4 cocok dengan no HP penerima
    if (!$last4 || substr($transaksii->penerima->phone, -4) !== $last4) {
        return response()->json(['message' => '4 digit terakhir nomor HP tidak sesuai'], 403);
    }

    return response()->json([
        'data' => $transaksii
    ]);
}

}
