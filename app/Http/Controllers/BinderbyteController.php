<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BinderbyteController extends Controller
{
    // Cek Ongkir (contoh: JNE dari Surabaya ke Jakarta)
    public function cekOngkir(Request $request)
    {
        $apiKey = env('BINDERBYTE_API_KEY');
        $response = Http::get("https://api.binderbyte.com/ongkir", [
            'api_key' => $apiKey,
            'courier' => 'jne',
            'origin' => '501', // ID kota asal
            'destination' => '114', // ID kota tujuan
            'weight' => 1000 // berat dalam gram
        ]);

        return response()->json($response->json());
    }
//     public function cekOngkir(Request $request)
// {
//     $apiKey = env('BINDERBYTE_API_KEY');

//     $response = Http::get("https://api.binderbyte.com/ongkir", [
//         'api_key'     => $apiKey,
//         'courier'     => $request->courier,         // contoh: jne
//         'origin'      => $request->origin,          // ID kota asal
//         'destination' => $request->destination,     // ID kota tujuan
//         'weight'      => $request->weight           // berat dalam gram
//     ]);

//     return response()->json($response->json());
// }


    // Cek Resi (tracking)
    public function cekResi(Request $request)
    {
        $apiKey = env('BINDERBYTE_API_KEY');
        $resi = $request->input('resi');
        $kurir = $request->input('kurir');

        $response = Http::get("https://api.binderbyte.com/v1/track", [
            'api_key' => $apiKey,
            'courier' => $kurir,
            'awb' => $resi
        ]);

        return response()->json($response->json());
    }
}
