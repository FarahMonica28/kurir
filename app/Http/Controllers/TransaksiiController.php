<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Province;
use App\Models\Transaksii;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\Invoice;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;

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
        


        $data = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota',])->with('kurir.user')
            ->when($request->search, function ($query, $search) {
                $query->where( 'no_resi', 'like', "%$search%")
                    // ->orwhere('no_resi', 'like', "%$search%")
                    ->orWhere('penerima', 'like', "%$search%")
                    //   ->orWhere('alamat_asal', 'like', "%$search%")
                    ->orWhere('alamat_tujuan', 'like', "%$search%")
                    ->orWhere('ekspedisi', 'like', "%$search%")
                    ->orWhere('layanan', 'like', "%$search%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            // Tambahkan ini ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            // ->when($request->has('pernah_digudang'), function ($query) {
            //     $query->where('pernah_digudang', true);
            // })
            ->when($request->has('pernah_digudang'), function ($q) {
                $q->where('pernah_digudang', true)
                ->orWhere('status', 'digudang');
            })

            // ->when($request->has('exclude_status'), function ($query) use ($request) {
            //     $query->where('status', '!=', $request->exclude_status);
            //     Log::info('a'); // log debug
            // })
            ->when($request->has('exclude_status'), function ($query) use ($request) {
                $excludeStatuses = $request->input('exclude_status');
                if (is_array($excludeStatuses)) {
                    $query->whereNotIn('status', $excludeStatuses);
                } else {
                    $query->where('status', '!=', $excludeStatuses);
                }
            })

            ->latest()
            
            // Paginate hasil query
            ->paginate($per);

        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }

        return response()->json($data);
    }

// Pindahkan ke constructor
    public function __construct()
    {
        Configuration::setXenditKey(config('services.xendit.secret'));
    }

    public function payment(Request $request)
    {
        $biaya = $request->input('biaya');

        if (!$biaya || !is_numeric($biaya) || $biaya <= 0) {
            return response()->json([
                'message' => 'Biaya ongkir belum valid',
            ], 400);
        }

        $order_id = 'ONGKIR-' . now()->timestamp . '-' . Str::random(4);

        $params = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => (int) $biaya,
            ],
            'item_details' => [
                [
                    'id' => $order_id,
                    'price' => (int) $biaya,
                    'quantity' => 1,
                    'name' => 'Biaya Ongkir Transaksi',
                ]
            ],
            'customer_details' => [
                'first_name' => $request->input('pengirim') ?? 'User',
                'email' => 'default@email.com',
            ],
            'callbacks' => [
                // 'finish' => url('/payment/success'),
                'finish' => env('APP_URL') . '/#/payment/success',
                'unfinish' => url('/payment/failed'),
                'error' => url('/payment/error'),
            ]
        ];

        $auth = base64_encode(env('MIDTRANS_SERVER_KEY'));
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Basic $auth",
        ])->withBody(json_encode($params), 'application/json')
        ->post('https://app.sandbox.midtrans.com/snap/v1/transactions');

        $data = json_decode($response->body());

        if (!isset($data->redirect_url)) {
            return response()->json([
                'message' => 'Gagal membuat link pembayaran Midtrans',
                'error' => $data,
            ], 500);
        }

        return response()->json([
            'redirect_url' => $data->redirect_url,
            'order_id' => $order_id,
        ]);
    }


    
    
//     public function paymentTemp(Request $request)
// {
//     $request->validate([
//         'pengirim' => 'required|string',
//         'penerima' => 'required|string',
//         'alamat_asal' => 'required|string',
//         'alamat_tujuan' => 'required|string',
//         'berat_barang' => 'required|numeric',
//         'biaya' => 'required|numeric|min:1000',
//     ]);

//     $orderId = 'ONGKIR-' . now()->timestamp . '-' . Str::random(4);

//     $params = [
//         'transaction_details' => [
//             'order_id' => $orderId,
//             'gross_amount' => (int) $request->biaya,
//         ],
//         'customer_details' => [
//             'first_name' => $request->pengirim,
//             'email' => 'user@example.com',
//         ],
//     ];

//     $auth = base64_encode(env('MIDTRANS_SERVER_KEY'));
//     $response = Http::withHeaders([
//         'Accept' => 'application/json',
//         'Authorization' => "Basic $auth",
//     ])->withBody(json_encode($params), 'application/json')
//       ->post('https://app.sandbox.midtrans.com/snap/v1/transactions');

//     if (!$response->successful()) {
//         return response()->json(['message' => 'Gagal membuat Snap Token'], 500);
//     }

//     $data = $response->json();

//     return response()->json([
//         'snap_token' => $data['token'] ?? null,
//         'redirect_url' => $data['redirect_url'] ?? null,
//         'order_id' => $orderId,
//     ]);
//     }




    // public function handleCallback(Request $request)
    // {
    //     $data = $request->all();

    //     if ($data['status'] === 'PAID') {
    //         $transaksiId = explode('-', $data['external_id'])[1];
    //         $transaksi = Transaksii::find($transaksiId);

    //         if ($transaksi) {
    //             $transaksi->status = 'dibayar';
    //             $transaksi->save();
    //         }
    //     }

    //     return response()->json(['message' => 'Callback diterima']);
    // }
    // Ambil data transaksii berdasarkan id

    
    public function get($id)
    {
        $transaksii = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota'])
        // $transaksii = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota', 'pengguna'])
            ->findOrFail($id);

        return response()->json($transaksii);
    }

    // Simpan atau update transaksii
    public function store(Request $request)
    {
        $transaksii = $request->validate([
            'penerima' => 'required|string',
            'pengirim' => 'required|string',
            'no_hp_penerima' => 'required|string',
            'tujuan_provinsi_id' => 'required|exists:provinces,id',
            'tujuan_kota_id' => 'required|exists:cities,id',
            'alamat_tujuan' => 'required|string',

            // 'pengguna_id' => 'nullable|exists:pengguna,pengguna_id',
            'nama_barang' => 'required|string',
            'berat_barang' => 'required|numeric|min:0.01',
            'ekspedisi' => 'required|string',
            'layanan' => 'required|string',
            'biaya' => 'required|integer',
            'asal_provinsi_id' => 'required|exists:provinces,id',
            'asal_kota_id' => 'required|exists:cities,id',
            'alamat_asal' => 'required|string',

            'waktu' => 'nullable|date|before_or_equal:now',
            'rating' => 'nullable|integer|min:1|max:5',
            'status' => 'nullable|string',
            // 'status' => 'nullable|in:menunggu,diproses,dikirim,selesai', 
            'komentar' => 'nullable|string',
            // 'kurir_id' => 'nullable|exists:kurir,kurir_id'
        ]);
        
        // Optional: cari pengguna untuk validasi tambahan (jika perlu)
        // $pengguna = Pengguna::where('user_id', $request->id)->first();
        // if (!$pengguna) {
        //     // Jika pengguna tidak ditemukan, kembalikan response error
        //     return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        // }

        // $kurir = auth()->user()->kurir;
        // Simpan transaksii baru
        Transaksii::create([
            'no_resi' => 'ABC-' . strtoupper(uniqid()),
            'nama_barang' => $request->nama_barang,
            'berat_barang' => $request->berat_barang, // pastikan field DB kamu memang 'berat_barat'
            'alamat_asal' => $request->alamat_asal,
            'alamat_tujuan' => $request->alamat_tujuan,
            'penerima' => $request->penerima,
            'pengirim' => $request->pengirim,
            'no_hp_penerima' => $request->no_hp_penerima,
            'status' => $request->status,
            'ekspedisi' => $request->ekspedisi,
            'layanan' => $request->layanan,
            'biaya' => $request->biaya,
            'rating' => $request->rating,
            'komentar' => $request->komentar,
            'waktu' => now()->format('Y-m-d H:i:s'),
            // 'pengguna_id' => $pengguna->pengguna_id,
            // 'pengguna_id' => auth()->id(), // atau $request->pengguna_id YANG VALID
            'asal_provinsi_id' => $request->asal_provinsi_id,
            'asal_kota_id' => $request->asal_kota_id,
            'tujuan_provinsi_id' => $request->tujuan_provinsi_id,
            'tujuan_kota_id' => $request->tujuan_kota_id,
            // 'kurir_id' => $kurir->kurir_id,
        ]);

        return response()->json([
            'message' => 'Berhasil menambahkan transaksii',
            'data' => $transaksii
        ]);
    }

    public function storePenilaian(Request $request)
    {
        // Validasi input dari request
        // - 'id' bersifat nullable, harus berupa integer dan ada di tabel transaksi
        // - 'rating' wajib diisi dan harus berupa string
        // - 'komentar' bersifat nullable dan harus berupa string jika ada
        $request->validate([
            'id' => 'nullable|integer|exists:transaksii,id', // Validasi id transaksi, integer dan harus ada di database
            'rating' => 'required|string', // Penilaian wajib diisi dan berupa string
            'komentar' => 'nullable|string', // Komentar opsional, jika ada harus berupa string
        ]);
    
        // Mencari transaksi berdasarkan ID yang diterima dari request
        $transaksii = Transaksii::find($request->id);
        // Jika transaksii tidak ditemukan, mengembalikan response 404 dengan pesan error
        if (!$transaksii) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }
    
        // Menyimpan rating dan komentar yang diterima dari request ke dalam transaksii
        $transaksii->rating = $request->rating;
        $transaksii->komentar = $request->komentar;
        
        // Menyimpan perubahan ke dalam database
        $transaksii->save();

        // Update rating kurir
        app(KurirController::class)->updateRating($transaksii->kurir_id);
    
        // Mengembalikan response sukses setelah rating berhasil disimpan
        return response()->json(['message' => 'Penilaian disimpan.']);
    }
    
    
    // TransaksiController.php
    public function antar(Request $request, $id)
    {
        $transaksii = Transaksii::findOrFail($id);
        
        $statusBaru = $request->status;

        // Update status
        $transaksii->status = $statusBaru;

        // Jika statusnya digudang, set kolom pernah_digudang = true
        if ($statusBaru === 'digudang') {
            $transaksii->pernah_digudang = true;
        }
        if (!$transaksii->kurir_id) {
        $transaksii->kurir_id = $request->kurir_id;
        }

        // Tambahkan pencatatan waktu sesuai status
        switch ($request->status) {
            case 'diambil kurir':
                $transaksii->waktu_diambil = now();
                break;
            case 'dikurir':
                $transaksii->waktu_dikurir = now();
                break;
            case 'digudang':
                $transaksii->waktu_digudang = now();
                break;
            case 'diproses':
                $transaksii->waktu_proses = now();
                break;
            case 'tiba digudang':
                $transaksii->waktu_tiba = now();
                break;
            case 'dikirim':
                $transaksii->waktu_kirim = now();
                break;
            case 'selesai':
                $transaksii->waktu_selesai = now();
                break;
        }

        // Update status dan kurir
        $transaksii->status = $request->status;
        $transaksii->kurir_id = $request->kurir_id;
        $transaksii->save();

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

    public function ambil(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);
        

        $transaksii = Transaksii::findOrFail($id);//
        $statusBaru = $request->status;

        // Update status
        $transaksii->status = $statusBaru;

        // Jika statusnya digudang, set kolom pernah_digudang = true
        if ($statusBaru === 'digudang') {
            $transaksii->pernah_digudang = true;
        }
        
        $user = auth()->user();

        switch ($request->status) {
            case 'diambil kurir':
                $transaksii->waktu_diambil = now();
                break;
            case 'dikurir':
                $transaksii->waktu_dikurir = now();
                break;
            case 'digudang':
                $transaksii->waktu_digudang = now();
                break;
            case 'diproses':
                $transaksii->waktu_proses = now();
                break;
            case 'tiba digudang':
                $transaksii->waktu_tiba = now();
                break;
            case 'dikirim':
                $transaksii->waktu_kirim = now();
                break;
            case 'selesai':
                $transaksii->waktu_selesai = now();
                break;
        }
        
        if ($transaksii->status !== 'digudang' && $request->status === 'digudang') {
            $transaksii->pernah_digudang = true;
        }
        $transaksii->status = $request->status;//
        $transaksii->save();//

        return response()->json([
            'message' => 'Status berhasil diubah',
            'status' => $transaksii->status,
        ]);
    }




}
