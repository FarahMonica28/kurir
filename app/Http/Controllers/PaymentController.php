<?php

namespace App\Http\Controllers;
// namespace App\Http\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaksii;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
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
        $transaksi = Transaksii::findOrFail($request->id);

        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION') === 'true';
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => 'ORDER-' . $transaksi->id,
                'gross_amount' => (int) $transaksi->biaya,
            ],
            'customer_details' => [
                'first_name' => $transaksi->pengirim,
                'email' => 'dummy@mail.com', // Optional
            ],
            'callbacks' => [
                'finish' => url('/transaksi/selesai/' . $transaksi->id),
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return response()->json([
            'redirect_url' => "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}",
        ]);
    }
}
