<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

class RajaOngkirService
{
    protected $key;
    protected $baseUrl;

    public function __construct()
    {
        $this->key = config('services.rajaongkir.key');
        $this->baseUrl = config('services.rajaongkir.base_url', 'https://api.rajaongkir.com/starter');
    }

    public function getAllProvinces()
    {
        $response = Http::withHeaders([
            'key' => $this->key,
        ])->get("{$this->baseUrl}/province");

        return $response->json('rajaongkir.results');
    }

    public function getCitiesByProvince($provinceId)
    {
        $response = Http::withHeaders([
            'key' => $this->key,
        ])->get("{$this->baseUrl}/city", [
            'province' => $provinceId
        ]);

        return $response->json('rajaongkir.results');
    }
}
// {
//     protected $apiKey;
//     protected $baseUrl;

//     public function __construct()
//     {
//         $this->apiKey = config('services.rajaongkir.key');
//         $this->baseUrl = config('services.rajaongkir.base_url');
//     }

//     public function getCitiesByProvince($provinceId)
//     {
//         $response = Http::withHeaders([
//             'key' => $this->apiKey,
//         ])->get("{$this->baseUrl}/city", [
//             'province' => $provinceId
//         ]);

//         return $response->json('rajaongkir.results');
//     }
// }


// {
//     protected $key;
//     protected $baseUrl;

//     public function __construct()
//     {
//         $this->key = config('services.rajaongkir.key');
//         $this->baseUrl = config('services.rajaongkir.base_url');
//     }

//     public function getProvinces()
//     {
//         return Http::withHeaders([
//             'key' => $this->key
//         ])->get($this->baseUrl . '/province')->json('rajaongkir.results');
//     }

//     public function getCities($provinceId = null)
//     {
//         $url = $this->baseUrl . '/city';
//         if ($provinceId) {
//             $url .= '?province=' . $provinceId;
//         }

//         return Http::withHeaders([
//             'key' => $this->key
//         ])->get($url)->json('rajaongkir.results');
//     }
// }
