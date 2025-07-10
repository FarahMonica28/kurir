<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Province;
use App\Models\Pengiriman;
use App\Models\Transaksii;
use App\Models\User;
use Midtrans\Config;
use Midtrans\Snap;
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
        


        $data = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota',])->with('pengiriman.kurir.user')->with('pengguna.user', 'kurir')
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

            ->when($request->pernah_digudang === 'true', function ($q) {
                $q->where('pernah_digudang', true);
            })

            // Tambahkan ini ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            ->when($request->has('pernah_digudang'), function ($q) {
                $q->where('pernah_digudang', true)
                ->orWhere('status', 'digudang');
            })
            // Tambahkan filter status_pembayaran settlement
            ->when($request->status_pembayaran, function ($q, $statusPembayaran) {
                $q->where('status_pembayaran', $statusPembayaran);
            })
            

            // Role: kurir — tampilkan transaksi yang belum ada kurir_id atau milik kurir itu sendiri 
            ->when(auth()->user()->role->name === 'kurir', function ($query) {
                Log::info('b'); // log debug
                $query->where(function ($q) {
                    Log::info('e'); // log debug
                    $kurirId = auth()->user()->kurir->kurir_id;
                    $q->whereNull('kurir_id')
                      ->orWhere('kurir_id', $kurirId);
                });
            })

            ->when(auth()->user()->role->name === 'pengguna', function ($query) {
                Log::info('pengguna'); // log debug
                $penggunaId = auth()->user()->pengguna->pengguna_id;
                $query->where('pengguna_id', $penggunaId);
            })


            ->latest()
            
            // Paginate hasil query
            ->paginate($per);

        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
            $ambilPengiriman = $item->pengiriman->firstWhere('status', 'ambil');
            $antarPengiriman = $item->pengiriman->firstWhere('status', 'antar');

            Log::info("INFO",["Ambil" => $ambilPengiriman]);
            Log::info("INFO",["Antar" => $antarPengiriman]);
            // Log::info("INFO",["Ambil" => $ambilPengiriman->kurir]);
            
            // Ambil user dari kurir masing-masing
            $item->ambil = $ambilPengiriman?->kurir->user;
            $item->antar = $antarPengiriman?->kurir->user;
        }

        return response()->json($data);
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
        $transaksii = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota'])->with('pengiriman.kurir.user')
        // $transaksii = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota', 'pengguna'])
            ->findOrFail($id);

        return response()->json($transaksii);
    }
    public function show($id)
    {
        $transaksii = Transaksii::with(['pengiriman.kurir.user'])->find($id);

        if (!$transaksii) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($transaksii);
    }

    // Simpan atau update transaksii

// public function store(Request $request)
// {
//     $transaksii = $request->validate([
//         'penerima' => 'required|string',
//         'pengirim' => 'required|string',
//         'no_hp_penerima' => 'required|string',
//         'tujuan_provinsi_id' => 'required|exists:provinces,id',
//         'tujuan_kota_id' => 'required|exists:cities,id',
//         'alamat_tujuan' => 'required|string',
//         'nama_barang' => 'required|string',
//         'berat_barang' => 'required|numeric|min:0.01',
//         'ekspedisi' => 'required|string',
//         'layanan' => 'required|string',
//         'biaya' => 'required|integer',
//         'asal_provinsi_id' => 'required|exists:provinces,id',
//         'asal_kota_id' => 'required|exists:cities,id',
//         'alamat_asal' => 'required|string',
//         'waktu' => 'nullable|date|before_or_equal:now',
//         'rating' => 'nullable|integer|min:1|max:5',
//         'status' => 'nullable|string',
//         'komentar' => 'nullable|string',
//         // 'kurir_id' => 'nullable|exists:kurir,kurir_id'
//     ]);

//     $noResi = 'ABC-' . strtoupper(uniqid());

//     // // Buat payload pembayaran ke Midtrans
//     // $payload = [
//     //     'transaction_details' => [
//     //         'order_id' => $noResi,
//     //         'gross_amount' => $request->biaya,
//     //     ],
//     //     'customer_details' => [
//     //         'first_name' => $request->pengirim,
//     //         'phone' => $request->no_hp_penerima,
//     //     ],
//     //     'enabled_payments' => ['gopay', 'bank_transfer'],
//     // ];

//     // // Kirim request ke Midtrans
//     $midtransResponse = Http::withBasicAuth(env('MIDTRANS_SERVER_KEY'), '')
//         ->post('https://api.sandbox.midtrans.com/v2/charge', array_merge($payload, ['payment_type' => 'bank_transfer', 'bank_transfer' => ['bank' => 'bca']]));

//     // if (!$midtransResponse->ok()) {
//     //     return response()->json(['message' => 'Gagal membuat transaksi di Midtrans'], 500);
//     // }

//     $midtransData = $midtransResponse->json();

//     // Tentukan status pembayaran berdasarkan response Midtrans
//     $statusPembayaran = $midtransData['transaction_status'] ?? 'pending';

//     // Simpan transaksi ke database
//     $transaksi = Transaksii::create([
//         'no_resi' => $noResi,
//         'nama_barang' => $request->nama_barang,
//         'berat_barang' => $request->berat_barang,
//         'alamat_asal' => $request->alamat_asal,
//         'alamat_tujuan' => $request->alamat_tujuan,
//         'penerima' => $request->penerima,
//         'pengirim' => $request->pengirim,
//         'no_hp_penerima' => $request->no_hp_penerima,
//         'status' => $request->status,
//         'ekspedisi' => $request->ekspedisi,
//         'layanan' => $request->layanan,
//         'biaya' => $request->biaya,
//         'rating' => $request->rating,
//         'komentar' => $request->komentar,
//         'waktu' => now(),
//         'asal_provinsi_id' => $request->asal_provinsi_id,
//         'asal_kota_id' => $request->asal_kota_id,
//         'tujuan_provinsi_id' => $request->tujuan_provinsi_id,
//         'tujuan_kota_id' => $request->tujuan_kota_id,
//         'status_pembayaran' => $statusPembayaran,
//         // 'kurir_id' => $request->kurir_id,
//     ]);

//     return response()->json([
//         'message' => 'Transaksi berhasil dibuat',
//         'data' => $transaksi,
//         'midtrans' => $midtransData // Kirim data Midtrans ke frontend jika perlu
//     ]);
// }



    public function store(Request $request)
{
    $validated = $request->validate([
        'penerima' => 'required|string',
        // 'pengirim' => 'required|string',
        'no_hp_penerima' => 'required|string',
        'tujuan_provinsi_id' => 'required|exists:provinces,id',
        'tujuan_kota_id' => 'required|exists:cities,id',
        'alamat_tujuan' => 'required|string',
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
        'komentar' => 'nullable|string',
        // 'kurir_id' => 'nullable|exists:kurir,kurir_id'
        'pengguna_id' => 'nullable|exits:pengguna,pengguna_id',
    ]);
    
    $noResi = 'ABC-' . strtoupper(uniqid());
    // $pengguna_id = Pengguna::where('user_id',auth()->id())->first('pengguna_id');
    // Pastikan user_id ada dalam request dan sesuai dengan relasi pada model
        $pengguna = Pengguna::where('user_id', $request->id)->first();
        if (!$pengguna) {
            // Jika pengguna tidak ditemukan, kembalikan response error
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }

    // Log::info($pengguna_id);

    // Simpan transaksi ke database
    $transaksii = Transaksii::create([
        'no_resi' => $noResi,
        'nama_barang' => $validated['nama_barang'],
        'berat_barang' => $validated['berat_barang'],
        'alamat_asal' => $validated['alamat_asal'],
        'alamat_tujuan' => $validated['alamat_tujuan'],
        'penerima' => $validated['penerima'],
        // 'pengirim' => $validated['pengirim'],
        'no_hp_penerima' => $validated['no_hp_penerima'],
        'status' => $validated['status'] ?? 'diproses',
        'ekspedisi' => $validated['ekspedisi'],
        'layanan' => $validated['layanan'],
        'biaya' => $validated['biaya'],
        'rating' => $validated['rating'] ?? null,
        'komentar' => $validated['komentar'] ?? null,
        'waktu' => now(),
        'asal_provinsi_id' => $validated['asal_provinsi_id'],
        'asal_kota_id' => $validated['asal_kota_id'],
        'tujuan_provinsi_id' => $validated['tujuan_provinsi_id'],
        'tujuan_kota_id' => $validated['tujuan_kota_id'],
        'status_pembayaran' => 'belum dibayar',
        // 'kurir_id' => $validated['kurir_id'] ?? null,
        // 'pengguna_id' => $validated['pengguna_id'],
        // 'pengguna_id' => $pengguna_id->pengguna_id, // ✅ ambil dari user yang login
        'pengguna_id' => $pengguna->pengguna_id // Mengaitkan transaksi dengan pengguna yang terkait

    ]);
    
    $transaksii->save();


    return response()->json([
        'message' => 'Transaksi berhasil dibuat',
        'data' => $transaksii,
    ]);
    }

    // public function storePenilaian(Request $request)
    // {
    //     // Validasi input dari request
    //     // - 'id' bersifat nullable, harus berupa integer dan ada di tabel transaksi
    //     // - 'rating' wajib diisi dan harus berupa string
    //     // - 'komentar' bersifat nullable dan harus berupa string jika ada
    //     $request->validate([
    //         'id' => 'nullable|integer|exists:transaksii,id', // Validasi id transaksi, integer dan harus ada di database
    //         'rating' => 'required|string', // Penilaian wajib diisi dan berupa string
    //         'komentar' => 'nullable|string', // Komentar opsional, jika ada harus berupa string
    //     ]);
    
    //     // Mencari transaksi berdasarkan ID yang diterima dari request
    //     $transaksii = Transaksii::find($request->id);
    //     // Jika transaksii tidak ditemukan, mengembalikan response 404 dengan pesan error
    //     if (!$transaksii) {
    //         return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
    //     }
    
    //     // Menyimpan rating dan komentar yang diterima dari request ke dalam transaksii
    //     $transaksii->rating = $request->rating;
    //     $transaksii->komentar = $request->komentar;
        
    //     // Menyimpan perubahan ke dalam database
    //     $transaksii->save();

    //     // Update rating kurir
    //     app(KurirController::class)->updateRating($transaksii->kurir_id);
    
    //     // Mengembalikan response sukses setelah rating berhasil disimpan
    //     return response()->json(['message' => 'Penilaian disimpan.']);
    // }
    
public function storePenilaian(Request $request)
{
    $request->validate([
        'id' => 'required|integer|exists:transaksii,id',
        'rating' => 'required|numeric|min:1|max:5',
        'komentar' => 'nullable|string',
    ]);

    // Cari transaksi berdasarkan ID (id transaksi, bukan id pengiriman)
    $transaksii = Transaksii::find($request->id);
    if (!$transaksii) {
        return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
    }

    // Simpan rating & komentar di transaksi (opsional, kalau kamu memang mau simpan di Transaksii juga)
    $transaksii->rating = $request->rating;
    $transaksii->komentar = $request->komentar;
    $transaksii->save();

    // Ambil semua pengiriman yg terkait transaksi ini
    $pengirimanList = Pengiriman::where('transaksii_id', $transaksii->id)->get();

    foreach ($pengirimanList as $pengiriman) {
        $pengiriman->rating = $request->rating;
        $pengiriman->komentar = $request->komentar;
        $pengiriman->save();

        // Update rata-rata rating kurir
        app(KurirController::class)->updateRating($pengiriman->kurir_id);
    }

    return response()->json(['message' => 'Penilaian disimpan untuk semua kurir yang terkait.']);
}


    
    // TransaksiController.php
//   use App\Models\Pengiriman;

    public function antar(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $transaksii = Transaksii::find($id);
        if (!$transaksii) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        $user = auth()->user();
        $kurir = $user->kurir;

        if (!$kurir) {
            return response()->json([
                'message' => 'Kurir belum terdaftar atau tidak punya relasi.',
            ], 403);
        }

        // Update transaksi
        $transaksii->kurir_id = $kurir->kurir_id;
        $transaksii->status = 'dikirim';
        $transaksii->waktu_kirim = now();
        $transaksii->save();

        $statusBaru = $request->status;

        Log::info('Status Update:', [$request->status]);

        switch ($statusBaru) {
            case 'dikirim':
                $transaksii->waktu_kirim = now();
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    // 'deskripsi' => 'Paket menuju ke alamat tujuan ' . ($transaksii->alamat_tujuan ?? '-'),
                    'status' => 'antar'
                ]);
                break;
            case 'selesai':
                $transaksii->waktu_selesai = now();
                // Pengiriman::create([
                //     'kurir_id' => $kurir->kurir_id,
                //     'transaksii_id' => $transaksii->id,
                //     'deskripsi' => 'Paket telah sampai ke tujuan',
                //     'status' => 'antar'
                // ]);
                break;
        }

        $transaksii->status = $statusBaru;
        $transaksii->save();

        // Muat ulang relasi kurir
        $transaksii->load('kurir');

        return response()->json([
            'message' => 'Status berhasil diubah.',
            'status' => $transaksii->status,
            'kurir' => $transaksii->kurir,
        ]);
    }



    // public function ambil(Request $request, $id)
    // {
    //     $request->validate([
    //         'status' => 'required|string',
    //     ]);
        

    //     $transaksii = Transaksii::findOrFail($id);//
    //     $statusBaru = $request->status;

    //     // Update status
    //     $transaksii->status = $statusBaru;

    //     // Jika statusnya digudang, set kolom pernah_digudang = true
    //     if ($statusBaru === 'digudang') {
    //         $transaksii->pernah_digudang = true;
    //     }
        
    //     
    //     }
        
    //     if ($transaksii->status !== 'digudang' && $request->status === 'digudang') {
    //         $transaksii->pernah_digudang = true;
    //     }
    //     $transaksii->status = $request->status;//
    //     $transaksii->save();//

    //     return response()->json([
    //         'message' => 'Status berhasil diubah',
    //         'status' => $transaksii->status,
    //     ]);
    // }

    public function ambil(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            // 'kurir_id' => 'required|string',
        ]);
        $transaksii = Transaksii::find($id);
        if (!$transaksii) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }


        // $transaksii = Transaksii::with('kurir')->findOrFail($id);
        $statusBaru = $request->status;

        $user = auth()->user();
        $kurir = $user->kurir;

        if (!$kurir) {
            return response()->json([
                'message' => 'Kurir belum terdaftar atau tidak punya relasi.',
            ], 403);
        }

        // Update transaksi
        $transaksii->kurir_id = $kurir->kurir_id;
        $transaksii->status = 'diambil kurir';
        $transaksii->waktu_diambil = now();
        $transaksii->save();


        Log::info('Status Update:', [$request->status]);

        switch ($request->status) {
            case 'diambil kurir':
                $transaksii->waktu_diambil = now();
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    // 'deskripsi' => 'Kurir sedang menuju rumahmu untuk mengambil barang'
                ]);
                break;

            case 'dikurir':
                $transaksii->waktu_dikurir = now();
                // Pengiriman::create([
                //     'kurir_id' => $kurir->kurir_id,
                //     'transaksii_id' => $transaksii->id,
                //     'deskripsi' => 'Kurir menuju gudang penempatan paket'
                // ]);
                break;

            case 'digudang':
                $transaksii->waktu_digudang = now();
                // Pengiriman::create([
                //     'kurir_id' => $kurir->kurir_id,
                //     'transaksii_id' => $transaksii->id,
                //     'deskripsi' => 'Paket telah sampai di gudang'
                // ]);
                break;
        }

        $transaksii->status = $request->status;
        $transaksii->save();



        // Update field waktu jika sesuai
        // if (isset($waktuMap[$statusBaru])) {
        //     $field = $waktuMap[$statusBaru];
        //     $transaksii->$field = now();
        // }

        // Tandai pernah masuk gudang
        if ($statusBaru === 'digudang') {
            $transaksii->pernah_digudang = true;
        }
        
        // // Update status transaksii
        // $transaksii->status = $statusBaru;
        // $transaksii->save();

        // Ambil ulang data kurir agar sinkron
        $transaksii->load('kurir');

        return response()->json([
            'message' => 'Status berhasil diubah.',
            'status' => $transaksii->status,
            'kurir' => $transaksii->kurir,
        ]);
    }

//     public function ambil(Request $request, $id)
// {
//     $transaksii = Transaksii::find($id);

//     if (!$transaksii) {
//         return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
//     }

//     $user = auth()->user();
//     $kurir = $user->kurir;

//     if (!$kurir) {
//         return response()->json([
//             'message' => 'Kurir belum terdaftar atau tidak punya relasi.',
//         ], 403);
//     }

//     // Update transaksi
//     $transaksii->kurir_id = $kurir->kurir_id;
//     $transaksii->status = 'diambil kurir';
//     $transaksii->waktu_diambil = now();
//     $transaksii->save();

//     // Tambahkan deskripsi pengiriman
//     Pengiriman::create([
//         'kurir_id' => $kurir->kurir_id,
//         'transaksii_id' => $transaksii->id,
//         'deskripsi' => 'Kurir sedang menuju rumahmu untuk mengambil barang'
//     ]);

//     return response()->json([
//         'message' => 'Barang berhasil diambil oleh kurir.',
//         'data' => $transaksii->load('kurir')
//     ]);
// }

    public function gudang(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $transaksii = Transaksii::find($id);
        if (!$transaksii) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        $statusBaru = $request->status;
        Log::info('Status Update:', [$statusBaru]);

        // Status yang tergolong aktivitas gudang
        $statusGudang = ['digudang', 'diproses', 'tiba digudang'];

        // Update waktu dan input pengiriman sesuai status
        switch ($statusBaru) {
            // case 'digudang':
            //     $transaksii->waktu_digudang = now();
            //     break;
                
                case 'diproses':
                    $transaksii->waktu_proses = now();
                    $transaksii->pernah_digudang = true; // Pastikan nilainya tidak null
                    Pengiriman::create([
                        'transaksii_id' => $transaksii->id,
                        'status' => 'gudang'
                    ]);
                break;

            case 'tiba digudang':
                $transaksii->waktu_tiba = now();
                break;
        }
        
        // Reset kurir_id setelah sampai di gudang
        if (in_array($statusBaru, $statusGudang)) {
            $transaksii->kurir_id = null;
        }
        
        $transaksii->status = $statusBaru;
        $transaksii->save();
        if ($statusBaru === 'digudang') {
            $transaksii->pernah_digudang = true;
        }

        return response()->json([
            'message' => 'Status berhasil diubah.',
            'status' => $transaksii->status,
            'pernah_digudang' => $transaksii->pernah_digudang,
        ]);
    }


        public function riwayat()
    {
        // Ambil user yang sedang login
        $user = auth()->user();
    
        // Jika user adalah kurir
        if ($user->role === 'kurir') {
            // Ambil ID kurir dari relasi user
            $kurirId = $user->kurir->kurir_id;
    
            // Ambil semua transaksi milik kurir ini yang sudah berstatus "Terkirim"
            // Diurutkan berdasarkan waktu pengiriman terbaru
            $transaksi = Transaksii::where('kurir_id', $kurirId)
                ->where('status', 'Terkirim')
                ->orderBy('waktu_terkirim', 'desc')
                ->get();
        }
    
        // Jika user adalah pengguna
        elseif ($user->role === 'pengguna') {
            // Ambil ID pengguna dari relasi user
            $penggunaId = $user->pengguna->pengguna_id;
    
            // Ambil semua transaksi milik pengguna ini yang sudah berstatus "Terkirim"
            // Diurutkan berdasarkan waktu pengiriman terbaru
            $transaksi = Transaksii::where('pengguna_id', $penggunaId)
                ->where('status', 'Terkirim')
                ->orderBy('waktu_terkirim', 'desc')
                ->get();
        }
    
        // Jika bukan kurir atau pengguna, kembalikan data kosong
        else {
            $transaksi = collect(); // empty collection
        }
    
        // Kembalikan hasil sebagai JSON response
        return response()->json($transaksi);
    }
}