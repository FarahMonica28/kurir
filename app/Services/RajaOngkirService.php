<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

class RajaOngkirService
    {
        protected $baseUrl;
        protected $apiKey;

        public function __construct()
        {
            $this->baseUrl = config('services.rajaongkir.base_url');
            $this->apiKey = config('services.rajaongkir.key');
        }

        public function getProvinces()
        {
            return Http::withHeaders(['key' => $this->apiKey])
                ->get("{$this->baseUrl}/province")
                ->json()['rajaongkir']['results'];
        }

        public function getCities($provinceId)
        {
            return Http::withHeaders(['key' => $this->apiKey])
                ->get("{$this->baseUrl}/city", ['province' => $provinceId])
                ->json()['rajaongkir']['results'];
        }

        public function getSubdistricts($cityId)
        {
            return Http::withHeaders(['key' => $this->apiKey])
                ->get("{$this->baseUrl}/subdistrict", ['city' => $cityId])
                ->json()['rajaongkir']['results'];
        }

        public function getCost($data)
        {
            return Http::withHeaders(['key' => $this->apiKey])
                ->post("{$this->baseUrl}/cost", $data)
                ->json()['rajaongkir']['results'];
        }
    }









// class RajaOngkirService
// {
//     protected $key;
//     protected $baseUrl;

//     public function __construct()
//     {
//         $this->key = config('services.rajaongkir.key');
//         $this->baseUrl = config('services.rajaongkir.base_url', 'https://api.rajaongkir.com/starter');
//     }

//     public function getAllProvinces()
//     {
//         $response = Http::withHeaders([
//             'key' => $this->key,
//         ])->get("{$this->baseUrl}/province");

//         return $response->json('rajaongkir.results');
//     }

//     public function getCitiesByProvince($provinceId)
//     {
//         $response = Http::withHeaders([
//             'key' => $this->key,
//         ])->get("{$this->baseUrl}/city", [
//             'province' => $provinceId
//         ]);

//         return $response->json('rajaongkir.results');
//     }
// }















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
