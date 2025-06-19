<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Xendit\Xendit;

class XenditController extends Controller
{
    public function buatInvoice(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'description' => 'required|string',
        ]);

        Xendit::setApiKey(env('XENDIT_SECRET'));

        $invoice = \Xendit\Invoice::create([
            'external_id' => 'invoice-ongkir-' . Str::random(10),
            'amount' => (int) $request->amount,
            'description' => $request->description,
        ]);

        return response()->json($invoice);
    }
}
