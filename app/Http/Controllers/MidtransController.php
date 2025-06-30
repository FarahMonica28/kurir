<?php

namespace App\Http\Controllers;

use App\Models\Transaksii;
use Auth;
use Illuminate\Http\Request;
use Midtrans\Notification;
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

    // public function handleNotification(Request $request)
    // {
    //     $notification = new Notification();

    //     $transaction = $notification->transaction_status;
    //     $type = $notification->payment_type;
    //     $orderId = $notification->order_id;
    //     $fraud = $notification->fraud_status;

    //     if ($transaction == 'capture' && $fraud == 'accept' || $transaction == 'settlement') {
    //         // ✅ Bayar sukses: simpan ke database sekarang
    //         // Contoh:
    //         Transaksii::create([
    //             'order_id' => $orderId,
    //             'user_id' => Auth::id(), // atau simpan dari frontend saat memanggil Snap
    //             'biaya' => $notification->gross_amount,
    //             'status' => 'diproses',
    //             // field lainnya...
    //         ]);
    //     } else if ($transaction == 'pending') {
    //         // ⏳ Masih pending, bisa abaikan
    //     } else if (in_array($transaction, ['deny', 'cancel', 'expire'])) {
    //         // ❌ Gagal bayar, abaikan atau log
    //     }

    //     return response()->json(['message' => 'Notifikasi diterima']);
    // }




public function handleNotification(Request $request)
{
    // Ambil notifikasi dari Midtrans
    $notification = new Notification();

    $transactionStatus = $notification->transaction_status;
    $orderId = $notification->order_id;

    // Cari transaksi di database
    $transaksi = Transaksii::find($orderId);
    if (!$transaksi) {
        return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
    }

    // Update status_pembayaran sesuai status dari Midtrans
    $transaksi->status_pembayaran = $transactionStatus;
    $transaksi->save();

    return response()->json(['message' => 'Notifikasi diproses']);
}

}
