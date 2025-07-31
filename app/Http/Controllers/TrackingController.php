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
    public function tracking($no_resi)
    {
        // $transaksii = Transaksii::where('no_resi', $no_resi)->with(['pengguna', 'kurir'])->with(['asalKota','tujuanKota','tujuanProvinsi','asalProvinsi'])->first();
    $transaksii = Transaksii::with('pengiriman')->where('no_resi', $no_resi)->with(['asalKota','tujuanKota','tujuanProvinsi','asalProvinsi', 'kurir', 'pengguna'])->first();
        // $transaksii = Transaksii::where('no_resi', $no_resi)->with(['asalKota','tujuanKota','tujuanProvinsi','asalProvinsi', 'kurir', 'pengguna'])->first();
    
        Log::info("info : ", ["info" => $transaksii]);

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

        return response()->json([
            'message' => 'Data transaksi ditemukan.',
            'data' => $transaksii
        ]);
    }
}
