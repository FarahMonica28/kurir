<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Models\Province;
use App\Models\Transaksii;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransaksiiController extends Controller
{
    // Ambil daftar provinsi dari RajaOngkir
    public function getProvinces()
    {
        // $response = Http::withHeaders([
        //     'key' => env('RAJAONGKIR_API_KEY'),
        // ])->get('https://api.rajaongkir.com/starter/province');

        // $provinces = collect($response['rajaongkir'])
        //     ->pluck('province', 'province_id');
        $provinces = Province::all()->pluck('name','id');

        return response()->json($provinces);
    }

    // Ambil daftar kota berdasarkan provinsi
    public function getCities($provinceId)
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY'),
        ])->get('https://api.rajaongkir.com/starter/city?province=' . $provinceId);

        $cities = collect($response['rajaongkir'])
            ->pluck('city_name', 'city_id');

        return response()->json($cities);
    }

    public function hitungOngkir(Request $request)
    {
        $response = Http::withHeaders([
            'key' => config('services.rajaongkir.key')
        ])->post('https://api.rajaongkir.com/starter/cost', [
            'origin' => $request->origin,
            'destination' => $request->destination,
            'weight' => $request->weight, // dalam gram
            'courier' => $request->courier, // jne / tiki / pos
        ]);

        Log::info("Cost", $response['rajaongkir']);

        $cost = $response['rajaongkir']['results'][0]['costs'];

        return response()->json($cost);
    }

     public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        // DB::statement('set @no=0+' . ($page - 1) * $per);
        DB::statement('set @no=0+' . $page * $per);


        $data = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota',])->with('pengguna')
            ->when($request->search, function ($query, $search) {
                $query->where( 'no_resi', 'like', "%$search%")
                    // ->orwhere('no_resi', 'like', "%$search%")
                    ->orWhere('penerima', 'like', "%$search%")
                    //   ->orWhere('alamat_asal', 'like', "%$search%")
                    ->orWhere('alamat_tujuan', 'like', "%$search%")
                    ->orWhere('ekspedisi', 'like', "%$search%")
                    ->orWhere('layanan', 'like', "%$search%");
            })
            // Role: pengguna — hanya tampilkan transaksi milik pengguna yang sedang login
            ->when(auth()->user()->role->name === 'pengguna', function ($query) {
                Log::info('pengguna'); // log debug
                $penggunaId = auth()->user()->id;
                Log::info("Pengguna ID : ", ["user" => $penggunaId]);
                $query->where('pengguna_id', $penggunaId);
            })

        // $query->orderBy('created_at', 'desc');

        // $data = $query->paginate($per);
        // Urutkan dari yang terbaru (created_at DESC)
            ->latest()
            
            // Paginate hasil query
            ->paginate($per);

        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }

        return response()->json($data);
    }

    // Ambil data transaksii berdasarkan id
    public function get($id)
    {
        $transaksii = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota', 'pengguna'])
            ->findOrFail($id);

        return response()->json($transaksii);
    }

    // Simpan atau update transaksii
    public function store(Request $request)
    {
        $transaksii = $request->validate([
            'penerima' => 'required|string',
            'no_hp_penerima' => 'required|string',
            'tujuan_provinsi_id' => 'required|exists:provinces,id',
            'tujuan_kota_id' => 'required|exists:cities,id',
            'alamat_tujuan' => 'required|string',

            'pengguna_id' => 'nullable|string:pengguna,pengguna_id',
            'nama_barang' => 'required|string',
            'berat_barang' => 'required|numeric|min:0.01',
            'ekspedisi' => 'required|string',
            'layanan' => 'required|string',
            'biaya' => 'required|integer',
            'asal_provinsi_id' => 'required|exists:provinces,id',
            'asal_kota_id' => 'required|exists:cities,id',
            'alamat_asal' => 'required|string',

            'waktu' => 'nullable|date|before_or_equal:now',
            'penilaian' => 'nullable|integer|min:1|max:5',
            'status' => 'nullable|string',
            // 'status' => 'nullable|in:menunggu,diproses,dikirim,selesai', 
            'komentar' => 'nullable|string',
        ]);
        
        // Optional: cari pengguna untuk validasi tambahan (jika perlu)
        $pengguna = Pengguna::where('user_id', $request->id)->first();
        if (!$pengguna) {
            // Jika pengguna tidak ditemukan, kembalikan response error
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }


        // Simpan transaksi baru
        Transaksii::create([
            'no_resi' => 'TRX-' . strtoupper(uniqid()),
            'nama_barang' => $request->nama_barang,
            'berat_barang' => $request->berat_barang, // pastikan field DB kamu memang 'berat_barat'
            'alamat_asal' => $request->alamat_asal,
            'alamat_tujuan' => $request->alamat_tujuan,
            'penerima' => $request->penerima,
            'no_hp_penerima' => $request->no_hp_penerima,
            'status' => $request->status,
            'biaya' => $request->biaya,
            'penilaian' => $request->penilaian,
            'komentar' => $request->komentar,
            'waktu' => now()->format('Y-m-d H:i:s'),
            // 'pengguna_id' => $pengguna->pengguna_id,
            'pengguna_id' => auth()->id(), // atau $request->pengguna_id YANG VALID
            'ekspedisi' => $request->ekspedisi,
            'layanan' => $request->layanan,
            'asal_provinsi_id' => $request->asal_provinsi_id,
            'asal_kota_id' => $request->asal_kota_id,
            'tujuan_provinsi_id' => $request->tujuan_provinsi_id,
            'tujuan_kota_id' => $request->tujuan_kota_id,
        ]);

        return response()->json([
            'message' => 'Berhasil menambahkan transaksi',
            'data' => $transaksii
        ]);
    }



    // Simpan transaksii baru atau update transaksii
    // public function store(Request $request, $id = null)
    // {
    //     $request->validate([
    //         'nama_barang' => 'required|string',
    //         'alamat_asal' => 'required|string',
    //         'alamat_tujuan' => 'required|string',
    //         'no_hp_penerima' => 'required|string',
    //         'penerima' => 'required|string',
    //         'province_origin' => 'required',
    //         'city_origin' => 'required',
    //         'province_destination' => 'required',
    //         'city_destination' => 'required',
    //     ]);

    //     $transaksii = $id ? Transaksi::findOrFail($id) : new Transaksi();

    //     $transaksii->pengguna_id = $request->id; // dari FormData
    //     $transaksii->nama_barang = $request->nama_barang;
    //     $transaksii->alamat_asal = $request->alamat_asal;
    //     $transaksii->alamat_tujuan = $request->alamat_tujuan;
    //     $transaksii->no_hp_penerima = $request->no_hp_penerima;
    //     $transaksii->penerima = $request->penerima;

    //     $transaksii->province_origin = $request->province_origin;
    //     $transaksii->city_origin = $request->city_origin;
    //     $transaksii->province_destination = $request->province_destination;
    //     $transaksii->city_destination = $request->city_destination;

    //     if (!$id) {
    //         $transaksii->status = 'belum terkirim';
    //         $transaksii->waktu = now();
    //     }

    //     $transaksii->save();

    //     return response()->json([
    //         'message' => $id ? 'Transaksi berhasil diperbarui' : 'Transaksi berhasil dibuat'
    //     ]);
    // }
}
