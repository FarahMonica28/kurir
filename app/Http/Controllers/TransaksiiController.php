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
        


        $data = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota',])->with('pengiriman.kurir.user')
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
            ->when($request->has('pernah_digudang'), function ($q) {
                $q->where('pernah_digudang', true)
                ->orWhere('status', 'digudang');
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
            $ambilPengiriman = $item->pengiriman->firstWhere('status', 'ambil');
            $antarPengiriman = $item->pengiriman->firstWhere('status', 'antar');

            Log::info("INFO",["Ambil" => $ambilPengiriman]);
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
        'pengirim' => 'required|string',
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
    ]);

    $noResi = 'ABC-' . strtoupper(uniqid());

    // Simpan transaksi ke database
    $transaksi = Transaksii::create([
        'no_resi' => $noResi,
        'nama_barang' => $validated['nama_barang'],
        'berat_barang' => $validated['berat_barang'],
        'alamat_asal' => $validated['alamat_asal'],
        'alamat_tujuan' => $validated['alamat_tujuan'],
        'penerima' => $validated['penerima'],
        'pengirim' => $validated['pengirim'],
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
        'status_pembayaran' => 'pending',
        // 'kurir_id' => $validated['kurir_id'] ?? null,
    ]);

    return response()->json([
        'message' => 'Transaksi berhasil dibuat',
        'data' => $transaksi,
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
//   use App\Models\Pengiriman;

public function antar(Request $request, $id)
{
    // $transaksii = Transaksii::findOrFail($id);
    // $statusBaru = $request->status;

    // // Update status transaksi
    // $transaksii->status = $statusBaru;

    // // Jika statusnya digudang, set kolom pernah_digudang = true
    // if ($statusBaru === 'digudang') {
    //     $transaksii->pernah_digudang = true;
    // }

     $request->validate([
        'status' => 'required|string',
    ]);

    $transaksii = Transaksii::with('kurir')->findOrFail($id);
    $statusBaru = $request->status;

    $user = auth()->user();
    $kurir = $user->kurir;

    if (!$kurir) {
        return response()->json([
            'message' => 'Kurir tidak ditemukan.',
        ], 403);
    }

    
    $transaksii->kurir_id = $kurir->kurir_id;
    // Simpan waktu status
        $user = auth()->user();

        switch ($request->status) {
            case 'diambil kurir':
                $transaksii->waktu_diambil = now();
                // Buat entri pengiriman dengan deskripsi khusus
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'deskripsi' => 'Kurir sedang menuju rumahmu untuk mengambil barang',
                    'status' => 'antar',
                ]);
                break;
            case 'dikurir':
                $transaksii->waktu_dikurir = now();
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'deskripsi' => 'Kurir menuju gudang penempatan paket',
                    'status' => 'antar',
                ]);
                break;
            case 'digudang':
                $transaksii->waktu_digudang = now();
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'deskripsi' => 'Paket telah sampai di gudang',
                    'status' => 'antar',
                ]);
                break;
            case 'diproses':
                $transaksii->waktu_proses = now();
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'deskripsi' => 'Paket akan dikirim ke provinsi ' . $transaksii->tujuanProvinsi->name . ' dan ke kota ' . $transaksii->asalKota->name,
                    'status' => 'antar',
                ]);
                break;
            case 'tiba digudang':
                $transaksii->waktu_tiba = now();
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'deskripsi' => 'Paket telah tiba digudang kota ' . $transaksii->asalKota->name,
                    'status' => 'antar',
                ]);
                break;
            case 'dikirim':
                $transaksii->waktu_kirim = now();
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'deskripsi' => 'Paket menuju ke alamat tujuan' . $transaksii->alamat_tujuan,
                    'status' => 'antar', 
                ]);
                break;
            case 'selesai':
                $transaksii->waktu_selesai = now();
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'deskripsi' => 'Paket telah sampai ke tujuan',
                    'status' => 'antar',
                ]);
                break;
            }

    $transaksii->save();

    // Cek apakah sudah ada pengiriman untuk kombinasi ini
    // $existing = Pengiriman::where('kurir_id', $request->kurir_id)
    //     ->where('transaksii_id', $transaksii->id)
    //     ->first();

    // if (!$existing) {
    //     Pengiriman::create([
    //         'kurir_id' => $request->kurir_id,
    //         'transaksii_id' => $transaksii->id,
    //         'deskripsi' => 'Mengantar Barang',
    //     ]);
    // }

    // return response()->json(['message' => 'Status berhasil diperbarui']);
     if ($statusBaru === 'digudang') {
        $transaksii->pernah_digudang = true;
    }

    // Update status transaksii
    $transaksii->status = $statusBaru;
    $transaksii->save();

    // Ambil ulang data kurir agar sinkron
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
        ]);
        $transaksii = Transaksii::find($id);
        if (!$transaksii) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }


        // $transaksii = Transaksii::with('kurir')->findOrFail($id);
        $statusBaru = $request->status;

        $user = auth()->user();
        $kurir = $user->kurir;

        // if (!$kurir) {
        //     return response()->json([
        //         'message' => 'Kurir tidak ditemukan.',
        //     ], 403);
        // }

        
        // $transaksii->kurir_id = $kurir->kurir_id;

        // Daftar field waktu sesuai status
        // $waktuMap = [
            //     'diambil kurir' => 'waktu_diambil',
            //     'dikurir' => 'waktu_dikurir',
            //     'digudang' => 'waktu_digudang',
            //     'diproses' => 'waktu_proses',
            //     'tiba digudang' => 'waktu_tiba',
            //     'dikirim' => 'waktu_kirim',
            //     'selesai' => 'waktu_selesai',
            // ];

            // Pengiriman::create([
            //     'kurir_id' => $kurir->kurir_id,
            //     'transaksii_id' =>$transaksii->id,
            //     'deskripsi' => "Mengambil Barang"
            // ]);
            
            $user = auth()->user();

        Log::info('Status Update:', [$request->status]);

        switch ($request->status) {
            case 'diambil kurir':
                $transaksii->waktu_diambil = now();
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'deskripsi' => 'Kurir sedang menuju rumahmu untuk mengambil barang'
                ]);
                break;

            case 'dikurir':
                $transaksii->waktu_dikurir = now();
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'deskripsi' => 'Kurir menuju gudang penempatan paket'
                ]);
                break;

            case 'digudang':
                $transaksii->waktu_digudang = now();
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'deskripsi' => 'Paket telah sampai di gudang'
                ]);
                break;

            case 'diproses':
                $transaksii->waktu_proses = now();
                Pengiriman::create([
                    // 'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'deskripsi' => 'Paket akan dikirim ke provinsi ' . $transaksii->tujuan_provinsi . ' dan ke kota ' . $transaksii->asal_kota
                ]);
                break;

            case 'tiba digudang':
                $transaksii->waktu_tiba = now();
                Pengiriman::create([
                    // 'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'deskripsi' => 'Paket telah tiba digudang kota ' . $transaksii->asal_kota
                ]);
                break;

            case 'dikirim':
                $transaksii->waktu_kirim = now();
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'deskripsi' => 'Paket menuju ke alamat tujuan ' . $transaksii->alamat_tujuan
                ]);
                break;

            case 'selesai':
                $transaksii->waktu_selesai = now();
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'deskripsi' => 'Paket telah sampai ke tujuan'
                ]);
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