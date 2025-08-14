<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Province;
use App\Models\City;
use App\Models\District;

class LocationsSeeder extends Seeder
{
    public function run(): void
    {
        $headers = [
            'Accept' => 'application/json',
            'key' => 'b896772462deb9ff63f38d89f32ab7b7',
        ];

        // --- Ambil dan simpan semua provinsi ---
        echo "Memeriksa dan menyimpan provinsi yang belum ada...\n";

        try {
            $provinceResponse = Http::withHeaders($headers)
                ->get('https://rajaongkir.komerce.id/api/v1/destination/province');
                

            if (!$provinceResponse->successful()) {
                echo "Gagal mengambil data provinsi.\n";
                return;
            }

            $provinces = $provinceResponse->json()['data'] ?? [];
            foreach ($provinces as $provinceData) {
                Province::updateOrCreate(
                    ['id' => $provinceData['id']],
                    ['name' => $provinceData['name']]
                );
            }

            echo "Pemeriksaan dan penyimpanan provinsi selesai.\n";
        } catch (\Exception $e) {
            echo "Error ambil provinsi: " . $e->getMessage() . "\n";
            return;
        }

        // --- Ambil dan simpan kota dari provinsi, mulai dari ID terakhir di tabel city ---
        echo "\nMemeriksa dan menyimpan kota yang belum ada...\n";

        $lastCity = City::orderBy('id', 'desc')->first();
        $startProvinceId = $lastCity ? $lastCity->province_id : null;

        $provincesToProcess = Province::when($startProvinceId, function ($query) use ($startProvinceId) {
            return $query->where('id', '>=', $startProvinceId);
        })->orderBy('id')->get();

        foreach ($provincesToProcess as $province) {
            echo "  - Proses provinsi '{$province->name}'...\n";

            try {
                $response = Http::withHeaders($headers)
                    ->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$province->id}");

                if (!$response->successful()) {
                    throw new \Exception("Gagal ambil kota untuk provinsi ID {$province->id}.");
                }

                $cities = $response->json()['data'] ?? [];

                foreach ($cities as $cityData) {
                    City::updateOrCreate(
                        ['id' => $cityData['id']],
                        [
                            'name' => $cityData['name'],
                            'province_id' => $province->id
                        ]
                    );
                }

                echo "    -> Kota untuk '{$province->name}' diproses.\n";
            } catch (\Exception $e) {
                echo "Error ambil kota: " . $e->getMessage() . "\n";
                return;
            }
        }

        // --- Ambil dan simpan kecamatan dari kota, mulai dari ID terakhir di tabel district ---
        echo "\nMemeriksa dan menyimpan kecamatan yang belum ada...\n";

        $lastDistrict = District::orderBy('city_id', 'desc')->first();
        $startCityId = $lastDistrict ? $lastDistrict->city_id : null;

        $citiesToProcess = City::when($startCityId, function ($query) use ($startCityId) {
            return $query->where('id', '>=', $startCityId);
        })->orderBy('id')->get();

        foreach ($citiesToProcess as $city) {
            echo "  - Proses kota '{$city->name}'...\n";

            try {
                $response = Http::withHeaders($headers)
                    ->get("https://rajaongkir.komerce.id/api/v1/destination/district/{$city->id}");

                if (!$response->successful()) {
                    throw new \Exception("Gagal ambil kecamatan untuk kota ID {$city->id}.");
                }

                $districts = $response->json()['data'] ?? [];

                foreach ($districts as $districtData) {
                    District::updateOrCreate(
                        ['id' => $districtData['id']],
                        [
                            'name' => $districtData['name'],
                            'city_id' => $city->id
                        ]
                    );
                }

                echo "    -> Kecamatan untuk '{$city->name}' diproses.\n";
            } catch (\Exception $e) {
                echo "Error ambil kecamatan: " . $e->getMessage() . "\n";
                return;
            }
        }

        echo "\n✅ Seeder Raja Ongkir selesai.\n";
    }
}