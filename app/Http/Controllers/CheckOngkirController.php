<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Province;
use Illuminate\Http\Request;
use dimaslanjaka\RajaOngkir\Facades\RajaOngkir;

class CheckOngkirController extends Controller
{
    // Ambil semua provinsi
    public function getProvinces()
    {
        $provinces = Province::pluck('name', 'province_id');
        return response()->json($provinces);
    }

    // Ambil kota berdasarkan ID provinsi
    public function getCities($province_id)
    {
        $cities = City::where('province_id', $province_id)->pluck('name', 'city_id');
        return response()->json($cities);
    }

    // Hitung ongkir berdasarkan input dari Vue
    public function checkOngkir(Request $request)
    {
        $validated = $request->validate([
            'city_origin' => 'required|integer',
            'city_destination' => 'required|integer',
            'weight' => 'required|integer',
            'courier' => 'required|string',
        ]);

        $cost = RajaOngkir::ongkosKirim([
            'origin'        => $validated['city_origin'],
            'destination'   => $validated['city_destination'],
            'weight'        => $validated['weight'],
            'courier'       => $validated['courier'],
        ])->get();

        return response()->json($cost);
    }
}
