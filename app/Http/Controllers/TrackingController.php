<?php

namespace App\Http\Controllers;

use App\Models\Transaksii;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Melacak status pengiriman berdasarkan no_resi.
     */
    public function track($no_resi)
    {
        // $transaksii = Transaksii::where('no_resi', $no_resi)->with(['pengguna', 'kurir'])->with(['asalKota','tujuanKota','tujuanProvinsi','asalProvinsi'])->first();
        $transaksii = Transaksii::where('no_resi', $no_resi)->with(['asalKota','tujuanKota','tujuanProvinsi','asalProvinsi'])->first();

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
