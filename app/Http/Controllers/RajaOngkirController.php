<?php

namespace App\Http\Controllers;

use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

// class RajaOngkirController extends Controller
// {
//     protected $rajaOngkir;

//     public function __construct(RajaOngkirService $rajaOngkir)
//     {
//         $this->rajaOngkir = $rajaOngkir;
//     }

//     public function provinces()
//     {
//         return response()->json($this->rajaOngkir->getProvinces());
//     }

//     public function cities($provinceId)
//     {
//         return response()->json($this->rajaOngkir->getCities($provinceId));
//     }

//     public function subdistricts($cityId)
//     {
//         return response()->json($this->rajaOngkir->getSubdistricts($cityId));
//     }

//     public function cost(Request $request)
//     {
//         $validated = $request->validate([
//             'origin' => 'required',
//             'originType' => 'required',
//             'destination' => 'required',
//             'destinationType' => 'required',
//             'weight' => 'required|numeric',
//             'courier' => 'required',
//         ]);

//         return response()->json($this->rajaOngkir->getCost($validated));
//     }
// }


// santri koding
class RajaOngkirController extends Controller
{
    /**
     * Menampilkan daftar provinsi dari API Raja Ongkir
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Mengambil data provinsi dari API Raja Ongkir
        $response = Http::withHeaders([

            //headers yang diperlukan untuk API Raja Ongkir
            'Accept' => 'application/json',
            'key' => config('rajaongkir.api_key'),

        ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');

        // Memeriksa apakah permintaan berhasil
        if ($response->successful()) {

            // Mengambil data provinsi dari respons JSON
            // Jika 'data' tidak ada, inisialisasi dengan array kosong
            $provinces = $response->json()['data'] ?? [];
        }

        // returning the view with provinces data
        return view('rajaongkir', compact('provinces'));
    }

    /**
     * Mengambil data kota berdasarkan ID provinsi
     *
     * @param int $provinceId
     * @return \Illuminate\Http\JsonResponse
     */


    public function getCities($provinceId)
    {
        // Mengambil data kota berdasarkan ID provinsi dari API Raja Ongkir
        $response = Http::withHeaders([

            //headers yang diperlukan untuk API Raja Ongkir
            'Accept' => 'application/json',
            'key' => config('rajaongkir.api_key'),

        ])->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$provinceId}");

        if ($response->successful()) {

            // Mengambil data kota dari respons JSON
            // Jika 'data' tidak ada, inisialisasi dengan array kosong
            return response()->json($response->json()['data'] ?? []);
        }
    }

    /**
     * Mengambil data kecamatan berdasarkan ID kota
     *
     * @param int $cityId
     * @return \Illuminate\Http\JsonResponse
     */

    public function getDistricts($cityId)
    {
        // Mengambil data kecamatan berdasarkan ID kota dari API Raja Ongkir
        $response = Http::withHeaders([

            //headers yang diperlukan untuk API Raja Ongkir
            'Accept' => 'application/json',
            'key' => config('rajaongkir.api_key'),

        ])->get("https://rajaongkir.komerce.id/api/v1/destination/district/{$cityId}");

        if ($response->successful()) {

            // Mengambil data kecamatan dari respons JSON
            // Jika 'data' tidak ada, inisialisasi dengan array kosong
            return response()->json($response->json()['data'] ?? []);
        }
    }

    /**
     * Menghitung ongkos kirim berdasarkan data yang diberikan
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    
    public function checkOngkir(Request $request)
    {
        $response = Http::asForm()->withHeaders([

            //headers yang diperlukan untuk API Raja Ongkir
            'Accept' => 'application/json',
            'key'    => config('rajaongkir.api_key'),

        ])->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'origin'      => 3855, // ID kecamatan Diwek (ganti sesuai kebutuhan)
                'destination' => $request->input('district_id'), // ID kecamatan tujuan
                'weight'      => $request->input('weight'), // Berat dalam gram
                'courier'     => $request->input('courier'), // Kode kurir (jne, tiki, pos)
        ]);

        if ($response->successful()) {

            // Mengambil data ongkos kirim dari respons JSON
            // Jika 'data' tidak ada, inisialisasi dengan array kosong
            return $response->json()['data'] ?? [];
        }
    }
}


// ini yang smapai tanggal 20
// class RajaOngkirController extends Controller
// {
//     protected $rajaOngkir;

//     public function __construct(RajaOngkirService $rajaOngkir)
//     {
//         $this->rajaOngkir = $rajaOngkir;
//     }

//     public function getProvinces()
//     {
//         $provinces = $this->rajaOngkir->getAllProvinces();

//         return response()->json($provinces);
//     }

//     public function getCities(Request $request)
//     {
//         $provinceId = $request->input('province'); // atau province_id
//         $response = Http::withHeaders([
//             'key' => config('services.rajaongkir.key')
//         ])->get('https://api.rajaongkir.com/starter/city', [
//             'province' => $provinceId
//         ]);

//         return response()->json($response->json());
//     }


//     public function getCost(Request $request)
//     {
//         $client = new \GuzzleHttp\Client();

//         $response = $client->post('https://api.rajaongkir.com/starter/cost', [
//             'headers' => [
//                 'key' => env('RAJAONGKIR_API_KEY'),
//             ],
//             'form_params' => [
//                 'origin' => $request->origin,
//                 'destination' => $request->destination,
//                 'weight' => $request->weight,
//                 'courier' => strtolower($request->courier), // pastikan ini dikirim dari frontend
//             ],
//         ]);

//         return response()->json(json_decode($response->getBody(), true));
//     }



//      public function cost(Request $request)
//     {
//         // Validasi input
//         $validated = $request->validate([
//             'origin' => 'required|string',
//             'destination' => 'required|string',
//             'weight' => 'required|integer|min:1',
//             'courier' => 'required|string',
//         ]);

//         $apiKey = env('RAJAONGKIR_API_KEY');  // Simpan API Key di .env

//         $response = Http::withHeaders([
//             'key' => $apiKey,
//         ])->post('https://api.rajaongkir.com/starter/cost', [
//             'origin' => $validated['origin'],
//             'destination' => $validated['destination'],
//             'weight' => $validated['weight'],
//             'courier' => $validated['courier'],
//         ]);

//         if ($response->failed()) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Gagal mengambil data ongkos kirim',
//                 'details' => $response->body(),
//             ], 500);
//         }

//         $result = $response->json();

//         return response()->json([
//             'status' => 'success',
//             'data' => $result['rajaongkir']['results'] ?? [],
//         ]);
//     }

//     public function cekOngkir(Request $request)
//     {
//         $response = Http::withHeaders([
//             'key' => config('services.rajaongkir.key'),
//         ])->post('https://api.rajaongkir.com/starter/cost', [
//             'origin' => $request->origin,
//             'destination' => $request->destination,
//             'weight' => $request->weight,
//             'courier' => $request->courier,
//         ]);

//         return $response->json()['rajaongkir']['results'][0]['costs'];
//     }


// }


