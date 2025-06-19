<?php

namespace App\Http\Controllers;

use App\Models\Transaksii;
use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Config;

class MidtransController extends Controller
{

public function bayar(Request $request)
{
    $transaksi = Transaksii::findOrFail($request->id);

    // Set konfigurasi Midtrans
    Config::$serverKey = env('MIDTRANS_SERVER_KEY');
    Config::$isProduction = false;

    $params = [
        'transaction_details' => [
            'order_id' => 'ORDER-' . $transaksi->id,
            'gross_amount' => (int) $transaksi->biaya,
        ],
        'customer_details' => [
            'first_name' => $transaksi->pengirim,
            'email' => 'customer@email.com', // Optional
        ],
        'callbacks' => [
            'finish' => route('transaksii.finish', $transaksi->id),
        ]
    ];

    $snapToken = Snap::getSnapToken($params);
    $snapUrl = "https://app.sandbox.midtrans.com/snap/v2/vtweb/" . $snapToken;

    return response()->json([
        'redirect_url' => $snapUrl,
    ]);
}

}
