<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShippingRate;
use Carbon\Carbon;
use GuzzleHttp\Client;

class ShippingController extends Controller
{
    public function getShippingCost($origin, $destination, $weight, $courier)
    {
        $cached = ShippingRate::where('origin_id', $origin)
            ->where('destination_id', $destination)
            ->where('weight', $weight)
            ->where('courier', $courier)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if ($cached) {
            return response()->json(['source' => 'cache', 'data' => $cached]);
        }

        // 🚀 Panggil API RajaOngkir jika tidak ditemukan
        $apiKey = env('RAJAONGKIR_API_KEY');
        $client = new Client();

        $response = $client->post('https://api.rajaongkir.com/starter/cost', [
            'headers' => [
                'key' => $apiKey,
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'origin' => $origin,
                'destination' => $destination,
                'weight' => $weight,
                'courier' => $courier,
            ],
        ]);

        $body = json_decode($response->getBody(), true);
        $result = $body['rajaongkir']['results'][0];

        $rate = $result['costs'][0]['cost'][0];

        $new = ShippingRate::create([
            'origin_id' => $origin,
            'destination_id' => $destination,
            'weight' => $weight,
            'courier' => $courier,
            'service_code' => $result['costs'][0]['service'],
            'service_name' => $result['costs'][0]['description'],
            'cost' => $rate['value'],
            'etd' => $rate['etd'],
            'note' => $rate['note'],
            'expired_at' => Carbon::now()->addHours(6),
        ]);

        return response()->json(['source' => 'api', 'data' => $new]);
    }
    public function checkOngkir(Request $request)
    {
        return $this->getShippingCost(
            $request->origin,
            $request->destination,
            $request->weight,
            $request->courier
        );
    }

}

// use Illuminate\Http\Request;
// use App\Models\ShippingRate;
// use Carbon\Carbon;
// use GuzzleHttp\Client;

// public function getShippingCost(Request $request)
// {
//     $validated = $request->validate([
//         'origin' => 'required|integer',
//         'destination' => 'required|integer',
//         'weight' => 'required|integer',
//         'courier' => 'required|string',
//     ]);

//     $origin = $validated['origin'];
//     $destination = $validated['destination'];
//     $weight = $validated['weight'];
//     $courier = $validated['courier'];

//     // Cek cache
//     $cached = ShippingRate::where('origin_id', $origin)
//         ->where('destination_id', $destination)
//         ->where('weight', $weight)
//         ->where('courier', $courier)
//         ->where('expired_at', '>', Carbon::now())
//         ->get();

//     if ($cached->isNotEmpty()) {
//         return response()->json(['source' => 'cache', 'data' => $cached]);
//     }

//     // Panggil API RajaOngkir
//     $client = new Client();
//     $apiKey = env('RAJAONGKIR_API_KEY');

//     try {
//         $response = $client->post('https://api.rajaongkir.com/starter/cost', [
//             'headers' => [
//                 'key' => $apiKey,
//                 'content-type' => 'application/x-www-form-urlencoded',
//             ],
//             'form_params' => [
//                 'origin' => $origin,
//                 'destination' => $destination,
//                 'weight' => $weight,
//                 'courier' => $courier,
//             ],
//         ]);

//         $body = json_decode($response->getBody(), true);
//         $result = $body['rajaongkir']['results'][0];

//         $savedRates = [];

//         foreach ($result['costs'] as $service) {
//             foreach ($service['cost'] as $detail) {
//                 $newRate = ShippingRate::create([
//                     'origin_id' => $origin,
//                     'destination_id' => $destination,
//                     'weight' => $weight,
//                     'courier' => $courier,
//                     'service_code' => $service['service'],
//                     'service_name' => $service['description'],
//                     'cost' => $detail['value'],
//                     'etd' => $detail['etd'],
//                     'note' => $detail['note'],
//                     'expired_at' => Carbon::now()->addHours(6),
//                 ]);

//                 $savedRates[] = $newRate;
//             }
//         }

//         return response()->json(['source' => 'api', 'data' => $savedRates]);

//     } catch (\Exception $e) {
//         return response()->json([
//             'error' => 'Gagal mengambil ongkir dari API RajaOngkir',
//             'message' => $e->getMessage()
//         ], 500);
//     }
// }
