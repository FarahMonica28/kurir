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
