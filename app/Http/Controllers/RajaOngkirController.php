<?php

namespace App\Http\Controllers;

use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class RajaOngkirController extends Controller
{
    protected $rajaOngkir;

    public function __construct(RajaOngkirService $rajaOngkir)
    {
        $this->rajaOngkir = $rajaOngkir;
    }

    public function getProvinces()
    {
        $provinces = $this->rajaOngkir->getAllProvinces();

        return response()->json($provinces);
    }

    public function getCities(Request $request)
    {
        $provinceId = $request->input('province'); // atau province_id
        $response = Http::withHeaders([
            'key' => config('services.rajaongkir.key')
        ])->get('https://api.rajaongkir.com/starter/city', [
            'province' => $provinceId
        ]);

        return response()->json($response->json());
    }


    public function getCost(Request $request)
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->post('https://api.rajaongkir.com/starter/cost', [
            'headers' => [
                'key' => env('RAJAONGKIR_API_KEY'),
            ],
            'form_params' => [
                'origin' => $request->origin,
                'destination' => $request->destination,
                'weight' => $request->weight,
                'courier' => strtolower($request->courier), // pastikan ini dikirim dari frontend
            ],
        ]);

        return response()->json(json_decode($response->getBody(), true));
    }



     public function cost(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'origin' => 'required|string',
            'destination' => 'required|string',
            'weight' => 'required|integer|min:1',
            'courier' => 'required|string',
        ]);

        $apiKey = env('RAJAONGKIR_API_KEY');  // Simpan API Key di .env

        $response = Http::withHeaders([
            'key' => $apiKey,
        ])->post('https://api.rajaongkir.com/starter/cost', [
            'origin' => $validated['origin'],
            'destination' => $validated['destination'],
            'weight' => $validated['weight'],
            'courier' => $validated['courier'],
        ]);

        if ($response->failed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data ongkos kirim',
                'details' => $response->body(),
            ], 500);
        }

        $result = $response->json();

        return response()->json([
            'status' => 'success',
            'data' => $result['rajaongkir']['results'] ?? [],
        ]);
    }

    public function cekOngkir(Request $request)
    {
        $response = Http::withHeaders([
            'key' => config('services.rajaongkir.key'),
        ])->post('https://api.rajaongkir.com/starter/cost', [
            'origin' => $request->origin,
            'destination' => $request->destination,
            'weight' => $request->weight,
            'courier' => $request->courier,
        ]);

        return $response->json()['rajaongkir']['results'][0]['costs'];
    }


}

// class RajaOngkirController extends Controller
// {
//     protected $rajaOngkir;

//     public function __construct(RajaOngkirService $rajaOngkir)
//     {
//         $this->rajaOngkir = $rajaOngkir;
//     }

//     public function getProvinces()
//     {
//         return response()->json($this->rajaOngkir->getProvinces());
//     }
    
//     // public function getCities(Request $request)
//     // {
//     //     $provinceId = $request->query('province_id', 11); // Default ke Jawa Timur
//     //     $response = Http::withHeaders([
//     //         'key' => env('RAJAONGKIR_API_KEY')
//     //     ])->get('https://api.rajaongkir.com/starter/city', [
//     //         'province' => $provinceId,
//     //     ]);

//     //     if ($response->successful()) {
//     //         return response()->json($response['rajaongkir']['results']);
//     //     }

//     //     return response()->json(['message' => 'Gagal ambil data kota'], 500);
//     // }

//     public function getCities(Request $request, RajaOngkirService $rajaOngkir)
//     {
//         $provinceId = $request->query('province_id');
//         $cities = $rajaOngkir->getCities($provinceId);
//         return response()->json($cities);
//     }

//     public function getKotaByProvinsi(RajaOngkirService $rajaOngkir)
//     {
//         $provinceId = 11; // Contoh: Jawa Timur
//         $cities = $rajaOngkir->getCitiesByProvince($provinceId);
//         return response()->json($cities);
//     }
// }
