<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kurir;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboardSummary()
    {
        // Jumlah kurir berdasarkan status
        $totalKurir = Kurir::count();
        $aktifKurir = Kurir::where('status', 'aktif')->count();
        $nonAktifKurir = Kurir::where('status', 'nonaktif')->count();

        // Jumlah transaksi
        $totalTransaksi = Transaksi::count();
        $transaksiSelesai = Transaksi::where('status', 'selesai')->count();
        $transaksiProses = Transaksi::where('status', 'proses')->count();

        // Rata-rata penilaian kurir
        $rataPenilaian = Transaksi::whereNotNull('penilaian')->avg('penilaian');

        return response()->json([
            'total_kurir' => $totalKurir,
            'aktif_kurir' => $aktifKurir,
            'nonaktif_kurir' => $nonAktifKurir,
            'total_transaksi' => $totalTransaksi,
            'transaksi_selesai' => $transaksiSelesai,
            'transaksi_proses' => $transaksiProses,
            'rata_penilaian' => round($rataPenilaian, 2),
        ]);
    }
}
