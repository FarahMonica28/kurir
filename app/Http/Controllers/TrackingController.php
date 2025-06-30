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
    public function track($no_resi)
    {
        // $transaksii = Transaksii::where('no_resi', $no_resi)->with(['pengguna', 'kurir'])->with(['asalKota','tujuanKota','tujuanProvinsi','asalProvinsi'])->first();
    
        $transaksii = Transaksii::where('no_resi', $no_resi)->with(['asalKota','tujuanKota','tujuanProvinsi','asalProvinsi', 'kurir', 'pengguna'])->first();
    
        Log::info("info : ", ["info" => $transaksii]);

        $ambilPengiriman = $transaksii->pengiriman->firstWhere('status', 'ambil');
        $antarPengiriman = $transaksii->pengiriman->firstWhere('status', 'antar');
            // Log::info("INFO",["Ambil" => $ambilPengiriman->kurir]);
            
            // Ambil user dari kurir masing-masing
            $transaksii->ambil = $ambilPengiriman?->kurir->user;
            $transaksii->antar = $antarPengiriman?->kurir->user;
        if (!$transaksii) {
            return response()->json([
                'message' => 'Transaksi dengan nomor resi tersebut tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'message' => 'Data transaksi ditemukan.',
            'data' => $transaksii
        ]);
    }
}
