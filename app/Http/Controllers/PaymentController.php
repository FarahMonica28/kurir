<?php

namespace App\Http\Controllers;
// namespace App\Http\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaksii;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Lcobucci\JWT\Configuration;
use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Midtrans\Notification;


class PaymentController extends Controller
{

    
    // PaymentController.php

    // routes/api.php

    public function handleWebhook(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $signatureKey = $request->signature_key;

        $expectedSignature = hash('sha512',
            $request->order_id .
            $request->status_code .
            $request->gross_amount .
            $serverKey
        );

        if ($signatureKey !== $expectedSignature) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Update transaksi berdasarkan no_resi (order_id)
        $transaksi = Transaksii::where('no_resi', $request->order_id)->first();
        if ($transaksi) {
            $transaksi->status_pembayaran = $request->transaction_status;
            $transaksi->save();
        }

        return response()->json(['message' => 'Webhook processed']);
    }


// MidtransController.php
// public function handleNotification(Request $request)
//     {
//         $payload = $request->all();
//         Log::info('Midtrans Webhook:', $payload); // Logging untuk debug

//         $orderId = $payload['order_id'] ?? null;
//         $transactionStatus = $payload['transaction_status'] ?? null;
//         $fraudStatus = $payload['fraud_status'] ?? null;

//         if (!$orderId || !$transactionStatus) {
//             return response()->json(['message' => 'Invalid payload'], 400);
//         }

//         // Cari transaksi berdasarkan no_resi
//         $transaksi = Transaksii::where('no_resi', $orderId)->first();

//         if (!$transaksi) {
//             return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
//         }

//         // Logika update status pembayaran
//         switch ($transactionStatus) {
//             case 'capture':
//                 if ($fraudStatus === 'challenge') {
//                     $transaksi->status_pembayaran = 'challenge';
//                 } else {
//                     $transaksi->status_pembayaran = 'success';
//                 }
//                 break;

//             case 'settlement':
//                 $transaksi->status_pembayaran = 'success';
//                 break;

//             case 'pending':
//                 $transaksi->status_pembayaran = 'pending';
//                 break;

//             case 'deny':
//             case 'expire':
//             case 'cancel':
//                 $transaksi->status_pembayaran = $transactionStatus;
//                 break;
//         }

//         $transaksi->save();

//         return response()->json(['message' => 'Notifikasi diproses'], 200);
//     }


// public function callback(Request $request)
//     {
//         $serverKey = config('midtrans.server_key');
//         $signatureKey = hash('sha512',
//             $request->order_id .
//             $request->status_code .
//             $request->gross_amount .
//             $serverKey
//         );

//         if ($signatureKey !== $request->signature_key) {
//             return response(['message' => 'Invalid signature'], 403);
//         }

//         $transaksii = Transaksii::find($request->order_id);

//         if (!$transaksii) {
//             return response(['message' => 'Transaksi tidak ditemukan'], 404);
//         }

//         // Update status pembayaran
//        $status = $request->transaction_status;

//         switch ($status) {
//             case 'settlement':
//             case 'capture':
//                 $transaksii->status_pembayaran = 'lunas';
//                 break;
//             case 'pending':
//                 $transaksii->status_pembayaran = 'pending';
//                 break;
//             case 'deny':
//             case 'expire':
//             case 'cancel':
//                 $transaksii->status_pembayaran = 'gagal';
//                 break;
//             default:
//                 $transaksii->status_pembayaran = 'unknown';
//         }


//         $transaksii->save();

//         return response(['message' => 'Callback diterima'], 200);
//     }

    public function callback(Request $request)
    {
        $notif = new Notification();

        $transaction = $notif->transaction_status;
        $type = $notif->payment_type;
        $orderId = $notif->order_id;
        $fraud = $notif->fraud_status;

        // Ambil id dari order_id
        $id = explode('-', $orderId)[1] ?? null;
        if (!$id) return response()->json(['error' => 'ID not found'], 400);

        $transaksii = Transaksii::find($id);
        if (!$transaksii) return response()->json(['error' => 'Transaksi tidak ditemukan'], 404);

        if ($transaction == 'capture' || $transaction == 'settlement') {
            $transaksii->status_pembayaran = 'paid';
        } elseif ($transaction == 'pending') {
            $transaksii->status_pembayaran = 'pending';
        } elseif (in_array($transaction, ['deny', 'expire', 'cancel'])) {
            $transaksii->status_pembayaran = 'failed';
        }

        $transaksii->save();

        return response()->json(['message' => 'Callback handled successfully']);
    }

// public function callback(Request $request)
// {
//     $serverKey = config('midtrans.server_key');
//     $hashed = hash('sha512', 
//         $request->order_id .
//         $request->status_code .
//         $request->gross_amount .
//         $serverKey
//     );

//     if ($hashed != $request->signature_key) {
//         return response(['message' => 'Invalid signature'], 403);
//     }

//     $transaction = Transaksii::where('order_id', $request->order_id)->first();
//     if (!$transaction) return response(['message' => 'Order not found'], 404);

//     if ($request->transaction_status === 'settlement') {
//         $transaction->status_pembayaran = 'settlement';
//         $transaction->save();
//     }
//     // Tambahkan else if untuk 'pending', 'expire', dll jika perlu

//     return response(['message' => 'Callback processed'], 200);
// }


// 1. Buat Snap Token (status awal pending)


    public function getSnapToken($id)
    {
        $transaksii = Transaksii::with(['pengguna'])->findOrFail($id);
        $transaksii->status_pembayaran = 'pending';
        $transaksii->save();

        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $transaksii->id,
                'gross_amount' => (int) $transaksii->biaya,
            ],
            'customer_details' => [
                'first_name' => $transaksii->pengirim,
                // 'email' => $transaksii->pengguna->email ?? 'user@gmail.com',
                // 'email' => $transaksii->pengguna_id ? ($transaksii->pengguna->email ?? 'user@gmail.com') : 'user@gmail.com',
                'email' => optional($transaksii->pengguna)->email ?: 'user@gmail.com',


            ]
        ];

        $snapToken = Snap::getSnapToken($params);
        return response()->json(['snap_token' => $snapToken]);
    }

// 2. Terima notifikasi dari Midtrans dan update status_pembayaran
public function handleNotification(Request $request)
{
    $notification = new Notification();

    $transactionStatus = $notification->transaction_status;
    $orderId = $notification->order_id;

    $transaksi = Transaksii::find($orderId);
    if (!$transaksi) {
        return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
    }

    $transaksi->status_pembayaran = $transactionStatus;
    $transaksi->save();

    return response()->json(['message' => 'Notifikasi diproses']);
}

public function manualUpdateStatus(Request $request)
{
    $transaksi = Transaksii::find($request->order_id);
    if ($transaksi) {
        $transaksi->status_pembayaran = $request->transaction_status ?? 'settlement';
        // $transaksi->payment_type = $request->payment_type ?? 'manual';
        $transaksi->save();

        \Log::info("Manual update: order_id={$request->order_id}, status={$transaksi->status_pembayaran}");
        return response()->json(['message' => 'Status pembayaran berhasil diperbarui']);
    }

    return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
}



public function show($id)
{
    // Ambil data transaksii
    $transaksii = Transaksii::with(['kurir', 'pengguna'])->findOrFail($id);

    // Konfigurasi Midtrans
    Config::$serverKey = env('MIDTRANS_SERVER_KEY');
    Config::$isProduction = false; // Ganti ke true jika production
    Config::$isSanitized = true;
    Config::$is3ds = true;

    // Buat parameter Snap
    $params = [
        'transaction_details' => [
            'order_id' => $transaksii->id,
            'gross_amount' => $transaksii->biaya, // pastikan ini integer, tanpa titik/koma
        ],
        'customer_details' => [
            'first_name' => $transaksii->pengguna->name ?? 'User',
            'email' => $transaksii->pengguna->email ?? 'user@example.com',
        ]
    ];

    // Buat Snap Token
    $snapToken = Snap::getSnapToken($params);

    // Kirim ke view atau langsung redirect
    return view('payment.show', compact('transaksii', 'snapToken'));
}

// public function handleNotification(Request $request)
// {
//     $notif = new \Midtrans\Notification();

//     $transaction = $notif->transaction_status;
//     $order_id = $notif->order_id;

//     $order = Transaksii::where('id', $order_id)->first();

//     if ($transaction == 'settlement' || $transaction == 'capture') {
//         $order->pembayaran = 'success';
//     } elseif ($transaction == 'pending') {
//         $order->pembayaran = 'pending';
//     } elseif (in_array($transaction, ['deny', 'expire', 'cancel'])) {
//         $order->pembayaran = 'failed';
//     }

//     $order->save();
//     return response()->json(['message' => 'Notifikasi diterima.']);
// }



    public function handleCallback(Request $request)
{
    $notif = new \Midtrans\Notification();

    $orderId = $notif->order_id;
    $status  = $notif->transaction_status;

    $transaksii = Transaksii::where('no_transaksii', $orderId)->first();

    if ($transaksii) {
        $transaksii->status_pembayaran = match ($status) {
            'pending' => 'pending',
            'settlement' => 'paid',
            'capture' => $notif->fraud_status == 'accept' ? 'paid' : 'challenge',
            'expire' => 'expired',
            'cancel', 'deny' => 'failed',
            default => 'unknown',
        };

        $transaksii->save();
    }

    return response()->json(['message' => 'OK']);
}

    // public function __construct()
    // {
    //     Configuration::setXenditKey(config('services.xendit.secret'));
    // }

    public function payment(Request $request)
{
    $biaya = $request->input('biaya');
 
    Config::$serverKey = env('MIDTRANS_SERVER_KEY');
    Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
    Config::$isSanitized = true;
    Config::$is3ds = true;

    if (!$biaya || !is_numeric($biaya) || $biaya <= 0) {
        return response()->json([
            'message' => 'Biaya ongkir belum valid',
        ], 400);
    }

    $user = Auth::user();
    $orderId = 'ONGKIR-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));

    $params = [
        'transaction_details' => [
            'order_id' => $orderId,
            'gross_amount' => (int) $biaya,
        ],
        'item_details' => [
            [
                'id' => $orderId,
                'price' => (int) $biaya,
                'quantity' => 1,
                'name' => 'Biaya Ongkir Transaksi',
            ]
        ],
        'customer_details' => [
            'first_name' => $request->input('pengirim') ?? 'User',
            'email' => $user?->email ?? 'default@email.com',
            'phone' => $user?->phone ?? '08123456789',
        ],
        'callbacks' => [
            // 'finish' => env('APP_URL') . '/#/payment/success',
            'finish' => env('APP_URL') . '/dashboard/transaksii/order',
            'unfinish' => env('APP_URL') . '/#/payment/failed',
            'error' => env('APP_URL') . '/#/payment/error',
        ]
    ];

    try {
        $snapResponse = Snap::createTransaction($params);
        return response()->json([
            'redirect_url' => $snapResponse->redirect_url,
            'order_id' => $orderId,
        ]);
    } catch (\Exception $e) {
        Log::error('Midtrans Error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Gagal membuat transaksii Midtrans',
            'error' => $e->getMessage(),
        ], 500);
    }
    }


public function redirectToMidtrans(Request $request)
{
    $transaksiiId = $request->input('id');

    $transaksii = Transaksii::findOrFail($transaksiiId);

    Config::$serverKey = env('MIDTRANS_SERVER_KEY');
    Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
    Config::$isSanitized = true;
    Config::$is3ds = true;

    $orderId = 'ONGKIR-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));

    $params = [
        'transaction_details' => [
            'order_id' => $orderId,
            'gross_amount' => (int) $transaksii->biaya,
        ],
        'item_details' => [
            [
                'id' => $transaksii->id,
                'price' => (int) $transaksii->biaya,
                'quantity' => 1,
                'name' => 'Biaya Ongkir Transaksi #' . $transaksii->id,
            ]
        ],
        'customer_details' => [
            'first_name' => $transaksii->pengirim ?? 'User',
            'email' => $transaksii->email_pengirim ?? 'default@email.com',
            'phone' => $transaksii->no_hp_pengirim ?? '08123456789',
        ],
        'callbacks' => [
            'finish' => env('APP_URL') . '/dashboard/transaksii/order',
            'unfinish' => env('APP_URL') . '/dashboard/transaksii/order',
            'error' => env('APP_URL') . '/dashboard/transaksii/order',
        ]
    ];

    try {
        $snapResponse = \Midtrans\Snap::createTransaction($params);
        return response()->json([
            'redirect_url' => $snapResponse->redirect_url,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal membuat transaksii Midtrans',
            'error' => $e->getMessage(),
        ], 500);
    }
}











    public function create (Request $request)
    {
        $params = array (
            'transaction_details' => array(
                'order_id' => Str::uuid(),
                'gross_amount' => $request -> price
            ),
            'item_detail' => array (
                array(
                    'price' => $request->price,
                    'quantity' => 1,
                    'name' => $request->item_name,
                )
            ),
            'customers_detail' => array(
                'customer' => $request->customer,
                'email' => $request->customer_email,
            ),
            // 'enabled_payment' => array('credit_cart', 'bca_va', 'bni_va', 'bri_va')
        );
        $auth = base64_encode(env('MIDTRANS_SERVER_KEY'));

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Basic $auth",
        ])->post('https://app.sandbox.midtrans.com/snap/v1/transactions', $params);

        // $response = json_decode($response->body());
        $response = json_decode($response->body());

        if (!isset($response->redirect_url)) {
            return response()->json([
                'error' => 'Midtrans error',
                'message' => $response,
            ], 500);
        }


        $payment = new Payment;
        $payment->order_id = $params['transaction_details']['order_id'];
        $payment->status = 'pending';
        $payment->price = $request->price;
        $payment->customer = $request->customer;
        $payment->customer_email = $request->customer_email;
        $payment->item_name = $request->item_name;
        // $payment->checkout_link = $response->redirect_url;
        $payment->checkout_link = $response->redirect_url;

        $payment->save();

        return response()->json($response);
    }


    
    public function pay(Request $request)
    {
        $transaksii = Transaksii::findOrFail($request->id);

        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION') === 'true';
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => 'ORDER-' . $transaksii->id,
                'gross_amount' => (int) $transaksii->biaya,
            ],
            'customer_details' => [
                'first_name' => $transaksii->pengirim,
                'email' => 'dummy@mail.com', // Optional
            ],
            'callbacks' => [
                'finish' => url('/transaksii/selesai/' . $transaksii->id),
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return response()->json([
            'redirect_url' => "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}",
        ]);
    }
}
