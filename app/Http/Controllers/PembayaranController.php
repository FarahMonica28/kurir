<?php
namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Transaksii;
use Illuminate\Http\Request;
use Str;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Xendit;
use Xendit\Invoice\Invoice;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Midtrans\Snap;
use Midtrans\Config;

class PembayaranController extends Controller
{
    // public function create(Request $request)
    // {
    //     // Validasi input
    //     $request->validate([
    //         'nama' => 'required|string',
    //         'amount' => 'required|numeric',
    //         'metode' => 'required|string',
    //     ]);

    //     // Contoh: VA via Xendit
    //     Xendit::setApiKey(env('xnd_development_JScuhZFamPXNsjNCtFdQdlsHOciRosytsapJoUeHE9zfAJGErI5f6uNkInMBUr'));

    //     $params = [
    //         'external_id' => 'va-' . time(),
    //         'bank_code' => 'BCA',
    //         'name' => $request->nama,
    //         'expected_amount' => $request->amount,
    //     ];

    //     try {
    //         $va = \Xendit\VirtualAccounts::create($params);
    //         return response()->json($va);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Gagal membuat pembayaran', 'error' => $e->getMessage()], 500);
    //     }
    // }
    


public function payment(Request $request)
{
    $id = $request->input('id');
    $transaksii = Transaksii::findOrFail($id);

    if (!$transaksii->biaya || $transaksii->biaya <= 0) {
        return response()->json([
            'message' => 'Biaya ongkir belum dihitung',
        ], 400);
    }

    // Konfigurasi Midtrans
    Config::$serverKey = env('MIDTRANS_SERVER_KEY');
    Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
    Config::$isSanitized = true;
    Config::$is3ds = true;

    $orderId = 'order-' . $transaksii->id . '-' . Str::random(6);

    $params = [
        'transaction_details' => [
            'order_id' => $orderId,
            'gross_amount' => (int) $transaksii->biaya,
        ],
        'customer_details' => [
            'first_name' => $transaksii->pengguna,
            'email' => 'user@example.com', // ganti jika ada email pengguna
        ],
    ];

    try {
        $snapToken = Snap::getSnapToken($params);

        // Simpan pembayaran
        $pembayaran = new Pembayaran();
        $pembayaran->transaksii_id = $transaksii->id;
        $pembayaran->external_id = $orderId;
        $pembayaran->snap_token = $snapToken;
        $pembayaran->status = 'pending';
        $pembayaran->save();

        return response()->json([
            'snap_token' => $snapToken
        ]);

    } catch (\Exception $e) {
        \Log::error('Midtrans Error:', ['message' => $e->getMessage()]);
        return response()->json([
            'message' => 'Gagal membuat pembayaran',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function store(Request $request)
    {
        // logika membuat invoice
        return response()->json([
            'invoice_url' => 'https://your-invoice-url.com'
        ]);
    }
    public function create(Request $request)
    {
        $amount = $request->input('amount'); // pastikan dari Vue
        $description = $request->input('description');

        // contoh: pakai Midtrans, Xendit, dll
        $invoiceUrl = 'https://dummy-payment-gateway.com/pay?id=123';
        // "invoice_url" : "https://some-payment-gateway.com/abc123";

        return response()->json([
            'invoice_url' => $invoiceUrl,
        ]);
        dd($invoiceUrl);
    }
    
    public function createInvoice(Request $request)
    
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'description' => 'required|string',
        ]);

        $params = [
            'external_id' => 'transaksii-' . time(),
            'payer_email' => auth()->user()->email,
            'description' => $request->description,
            'amount' => (int) $request->amount,
            'success_redirect_url' => route('payment.success'),
        ];

        $invoice = \Xendit\Invoice::create($params);

        return response()->json([
            'invoice_url' => $invoice['invoice_url'],
            'id' => $invoice['id'],
        ]);
    }
}


