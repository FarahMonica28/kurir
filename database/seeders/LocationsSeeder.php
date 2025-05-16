<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Province;
use App\Models\City;
use Illuminate\Support\Facades\Log;

class LocationsSeeder extends Seeder
{
    protected $apiKey = '';
    protected $baseUrl = '';

    public function __construct()
    {
        // $this->apiKey = config('services.rajaongkir.key');
        $this->apiKey = config('services.rajaongkir.key');
        // $this->baseUrl = config('services.rajaongkir.base_url', 'https://api.rajaongkir.com/starter');
        $this->baseUrl = config('services.rajaongkir.base_url', 'https://api.rajaongkir.com/starter');

    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // dd($this->apiKey); // ← Tambahkan di sini

        // $provincesResponse = Http::withHeaders([
        //     'key' => $this->apiKey,
        // ])->get("{$this->baseUrl}/province");
        $provincesResponse = Http::withHeaders([
        'key' => $this->apiKey,
        ])->get("{$this->baseUrl}/province");

        // Cek apakah gagal ambil data
        if (!$provincesResponse->successful()) {
            dd('Gagal ambil data provinsi', $provincesResponse->status(), $provincesResponse->body());
        }

        dd("Berhasil",$provincesResponse->status(),$provincesResponse->body());

        $daftarProvinsi = $provincesResponse->json('rajaongkir.results');

        foreach ($daftarProvinsi as $provinceRow) {
            // Simpan provinsi
            Province::create([
                'province_id' => $provinceRow['province_id'],
                'name'        => $provinceRow['province'],
            ]);

            // Ambil kota dari provinsi
            $citiesResponse = Http::withHeaders([
                'key' => $this->apiKey,
            ])->get("{$this->baseUrl}/city", [
                'province' => $provinceRow['province_id']
            ]);

            $daftarKota = $citiesResponse->json('rajaongkir.results');

            foreach ($daftarKota as $cityRow) {
                City::create([
                    'province_id'   => $provinceRow['province_id'],
                    'city_id'       => $cityRow['city_id'],
                    'name'          => $cityRow['city_name'],
                ]);
            }
        }
    }
}


// use Illuminate\Database\Seeder;
// use Kavist\RajaOngkir\Facades\RajaOngkir;
// use App\Models\Province;
// use App\Models\City;

// class LocationsSeeder extends Seeder
// {
//     /**
//      * Run the database seeds.
//      *
//      * @return void
//      */
//     public function run()
//     {
//         $daftarProvinsi = RajaOngkir::provinsi()->all();
//         foreach ($daftarProvinsi as $provinceRow) {
//             Province::create([
//                 'province_id' => $provinceRow['province_id'],
//                 'name'        => $provinceRow['province'],
//             ]);
//             $daftarKota = RajaOngkir::kota()->dariProvinsi($provinceRow['province_id'])->get();
//             foreach ($daftarKota as $cityRow) {
//                 City::create([
//                     'province_id'   => $provinceRow['province_id'],
//                     'city_id'       => $cityRow['city_id'],
//                     'name'          => $cityRow['city_name'],
//                 ]);
//             }
//         }
//     }
// }

// use Illuminate\Database\Seeder;
// use App\Models\Province;
// use App\Models\City;
// use App\Services\RajaOngkirService;

// class LocationsSeeder extends Seeder
// {
//     public function run(RajaOngkirService $rajaOngkir)
//     {
//         $daftarProvinsi = $rajaOngkir->getAllProvinces();

//         foreach ($daftarProvinsi as $provinceRow) {
//             Province::create([
//                 'province_id' => $provinceRow['province_id'],
//                 'name'        => $provinceRow['province'],
//             ]);

//             $daftarKota = $rajaOngkir->getCitiesByProvince($provinceRow['province_id']);

//             foreach ($daftarKota as $cityRow) {
//                 City::create([
//                     'province_id'   => $provinceRow['province_id'],
//                     'city_id'       => $cityRow['city_id'],
//                     'name'          => $cityRow['city_name'],
//                 ]);
//             }
//         }
//     }
// }
