<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\District;
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
            // $response = Http::withHeaders([
            //     'key' => env('RAJAONGKIR_API_KEY'),
            // ])->get('https://api.rajaongkir.com/starter/city?province=' . $provinceId);

            // $cities = collect($response['rajaongkir'])
            //     ->pluck('name', 'id');
            // $cities = City::all()->pluck('name','id');
            $cities = City::where('province_id', $provinceId)->pluck('name', 'id');


            return response()->json($cities);
        }

        public function getDistricts($cityId)
        {
            // $response = Http::withHeaders([
            //     'key' => env('RAJAONGKIR_API_KEY'),
            // ])->get('https://pro.rajaongkir.com/api/subdistrict?city=' . $cityId);

            // $districts = collect($response['rajaongkir']['results'])
            //     ->pluck('name', 'id');
            // $districts = District::all()->pluck('name','id');
            $districts = District::where('city_id', $cityId)->pluck('name', 'id');

            

            return response()->json($districts);
        }


        public function checkOngkir(Request $request)
        {
            Log::info('Check Ongkir', [
                'origin' => $request->input('origin'),
                'destination' => $request->input('destination'),
                'weight' => $request->input('weight'),
                'courier' => $request->input('courier'),
            ]);

            //tambahkan ini
            $response = Http::asForm()->withHeaders([
                'key' => 'Iq46S5Xid89d001ddb978fd9VRa0Z0bw', // Ambil dari config/ENV
                'Accept' => 'application/json',
            ])->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
            // ])->post('https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost', [
                'origin'      => $request->input('origin'),       // ID kecamatan asal  
                'destination' => $request->input('destination'),  // ID kecamatan tujuan
                'weight'      => $request->input('weight'),       // Berat gram
                'courier'     => $request->input('courier'),      // jne, tiki, pos
                'price'       => "lowest", // lowest / highest, default lowest 
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['data'])) {
                return response()->json($data['data'], 200);
            }

            return response()->json([
                'error' => 'Gagal mengambil data ongkir',
                'response' => $data
            ], 500);
        }





    
    public function get($id)
    {
        $transaksii = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota', 'asalKecamatan', 'tujuanKecamatan'])->with('pengiriman.kurir.user')
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

    public function index(Request $request)
    {
        // Ambil parameter pagination: jumlah data per halaman (`per`) dan halaman ke-berapa (`page`)
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        // Atur nomor urut dimulai dari (halaman sekarang * jumlah per halaman)
        DB::statement('set @no=0+' . $page * $per);

        // Ambil data transaksi lengkap dengan relasi: provinsi/kota asal-tujuan, pengiriman, pengguna, dan kurir
        $data = Transaksii::with([
                'asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota', 'asalKecamatan', 'tujuanKecamatan',
                'pengiriman.kurir.user', // relasi ke kurir pengiriman
                'pengguna.user', 'kurir'
            ])
            // Filter pencarian berdasarkan kolom-kolom tertentu
            ->when($request->search, function ($query, $search) {
                $query->where('no_resi', 'like', "%$search%")
                    ->orWhere('penerima', 'like', "%$search%")
                    ->orWhere('alamat_tujuan', 'like', "%$search%")
                    ->orWhere('ekspedisi', 'like', "%$search%")
                    ->orWhere('layanan', 'like', "%$search%");
            })
            // Filter berdasarkan status (misalnya: 'dikirim', 'selesai')
            ->when($request->status, function ($query, $status) {  
                Log::info($status); 
                $query->where('status', $status);
            })
            // Filter untuk mengecualikan status tertentu
            ->when($request->has('exclude_status'), function ($query) use ($request) {
                Log::info("Exclude");
                $excludeStatuses = $request->input(key: 'exclude_status');
                if (is_array($excludeStatuses)) {
                    $query->whereNotIn('status', $excludeStatuses);
                } else {
                    $query->where('status', '!=', $excludeStatuses);
                }
            })
            // Filter untuk hanya menampilkan transaksi yang pernah masuk gudang
            ->when($request->pernah_digudang === 'true', function ($q) {
                Log::info("Pernah Digudang");
                $q->where('pernah_digudang', true);
            })
            // Alternatif tambahan, jika `pernah_digudang` ada di query, tampilkan juga status `digudang`
            ->when($request->has('pernah_digudang'), function ($q) {
                Log::info("Pernah Digudang 2");
                $q->where('pernah_digudang', true)
                ->orWhere('status', 'digudang');
            })
            // Filter berdasarkan status pembayaran (contoh: 'settlement', 'pending')
            ->when($request->status_pembayaran, function ($q, $statusPembayaran) {
                Log::info("Status Pembayaran");
                $q->where('status_pembayaran', $statusPembayaran);
            })
            // Jika user adalah kurir, tampilkan hanya transaksi tanpa kurir_id atau miliknya sendiri
            ->when(auth()->user()->role->name === 'kurir', function ($query) {
                Log::info('b');
                $query->where(function ($q) {
                    Log::info('e');
                    $kurirId = auth()->user()->kurir->kurir_id;
                    $q->whereNull('kurir_id')
                    ->orWhere('kurir_id', $kurirId);
                });
            })
            // Jika user adalah pengguna, tampilkan hanya transaksi miliknya
            ->when(auth()->user()->role->name === 'pengguna', function ($query) {
                Log::info('pengguna');
                $penggunaId = auth()->user()->pengguna->pengguna_id;
                $query->where('pengguna_id', $penggunaId);
            })
            ->latest() // urutkan berdasarkan waktu terbaru
            ->paginate($per); // paginasi hasil

        // Menambahkan nomor urut ke setiap item dalam hasil
        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;

            // Ambil kurir untuk status "ambil" dan "antar"
            $ambilPengiriman = $item->pengiriman->firstWhere('status', 'ambil');
            $antarPengiriman = $item->pengiriman->firstWhere('status', 'antar');

            // Tambahkan data kurir user ke item
            $item->ambil = $ambilPengiriman?->kurir->user;
            $item->antar = $antarPengiriman?->kurir->user;
        }

        return response()->json($data);
    }

    public function store(Request $request)
    {
        // Validasi input request
        $validated = $request->validate([
            'penerima' => 'required|string',
            'no_hp_penerima' => 'required|string',
            'no_hp_pengirim' => 'required|string',
            'tujuan_provinsi_id' => 'required|exists:provinces,id',
            'tujuan_kota_id' => 'required|exists:cities,id',
            'tujuan_kecamatan_id' => 'required|exists:districts,id',
            'alamat_tujuan' => 'required|string',
            'nama_barang' => 'required|string',
            'berat_barang' => 'required|numeric|min:0.01',
            'ekspedisi' => 'required|string',
            'layanan' => 'required|string',
            'biaya' => 'required|integer',
            'asal_provinsi_id' => 'required|exists:provinces,id',
            'asal_kota_id' => 'required|exists:cities,id',
            'asal_kecamatan_id' => 'required|exists:districts,id',
            'alamat_asal' => 'required|string',
            'waktu' => 'nullable|date|before_or_equal:now',
            'rating' => 'nullable|integer|min:1|max:5',
            'status' => 'nullable|string',
            'komentar' => 'nullable|string',
            'pengguna_id' => 'nullable|exits:pengguna,pengguna_id',
        ]);

        // Buat nomor resi unik
        $noResi = 'ABC-' . strtoupper(uniqid());

        // Cari pengguna berdasarkan user ID
        $pengguna = Pengguna::where('user_id', $request->id)->first();
        if (!$pengguna) {
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }

        // Simpan transaksi ke database
        $transaksii = Transaksii::create([
            'no_resi' => $noResi,
            'nama_barang' => $validated['nama_barang'],
            'berat_barang' => $validated['berat_barang'],
            'alamat_asal' => $validated['alamat_asal'],
            'alamat_tujuan' => $validated['alamat_tujuan'],
            'penerima' => $validated['penerima'],
            'no_hp_penerima' => $validated['no_hp_penerima'],
            'no_hp_pengirim' => $validated['no_hp_pengirim'],
            'status' => $validated['status'] ?? 'diproses',
            'ekspedisi' => $validated['ekspedisi'],
            'layanan' => $validated['layanan'],
            'biaya' => $validated['biaya'],
            'rating' => $validated['rating'] ?? null,
            'komentar' => $validated['komentar'] ?? null,
            'waktu' => now(),
            'asal_provinsi_id' => $validated['asal_provinsi_id'],
            'asal_kota_id' => $validated['asal_kota_id'],
            'asal_kecamatan_id' => $validated['asal_kecamatan_id'],
            'tujuan_provinsi_id' => $validated['tujuan_provinsi_id'],
            'tujuan_kota_id' => $validated['tujuan_kota_id'],
            'tujuan_kecamatan_id' => $validated['tujuan_kecamatan_id'],
            'status_pembayaran' => 'pending',
            'pengguna_id' => $pengguna->pengguna_id
        ]);

        return response()->json([
            'message' => 'Transaksi berhasil dibuat',
            'data' => $transaksii,
        ]);
    }

    public function ambil(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        // $transaksii = Transaksii::find($id);
        $transaksii = Transaksii::with(['pengguna'])->findOrFail($id);

        if (!$transaksii) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        // Ambil kurir dari user yang login
        $user = auth()->user();
        $kurir = $user->kurir;

        if (!$kurir) {
            return response()->json(['message' => 'Kurir belum terdaftar atau tidak punya relasi.'], 403);
        }

        // Update status transaksi ke 'diambil kurir'
        $transaksii->kurir_id = $kurir->kurir_id;
        $transaksii->status = 'diambil kurir';
        $transaksii->waktu_diambil = now();
        $transaksii->save();

        $statusBaru = $request->status;

        // Tindakan berdasarkan status
        switch ($statusBaru) {
            case 'diambil kurir':
                $transaksii->waktu_diambil = now();
                Pengiriman::create([
                    'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                ]);
                break;

            case 'dikurir':
                $transaksii->waktu_dikurir = now();
                break;

            case 'digudang':
                $transaksii->waktu_digudang = now();
                Pengiriman::create([
                    // 'kurir_id' => $kurir->kurir_id,
                    'transaksii_id' => $transaksii->id,
                    'status' => 'gudang'
                ]);
                // $transaksii->pernah_digudang = true;
                $transaksii->kurir_id = null;
                break;
        }

        $transaksii->status = $statusBaru;
        $transaksii->save();

        return response()->json([
            'message' => 'Status berhasil diubah.',
            'status' => $transaksii->status,
            'kurir' => $transaksii->kurir,
        ]);
    }

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
            return response()->json(['message' => 'Kurir belum terdaftar atau tidak punya relasi.'], 403);
        }

        // Update status ke 'dikirim'
        $transaksii->kurir_id = $kurir->kurir_id;
        $transaksii->status = 'dikirim';
        $transaksii->waktu_kirim = now();
        $transaksii->save();

        $statusBaru = $request->status;

        // Jika statusnya 'dikirim', catat pengiriman
        if ($statusBaru === 'dikirim') {
            Pengiriman::create([
                'kurir_id' => $kurir->kurir_id,
                'transaksii_id' => $transaksii->id,
                'status' => 'antar'
            ]);
        }

        if ($statusBaru === 'selesai') {
            $transaksii->waktu_selesai = now();
        }

        $transaksii->status = $statusBaru;
        $transaksii->save();

        // Reload data relasi kurir
        $transaksii->load('kurir');

        return response()->json([
            'message' => 'Status berhasil diubah.',
            'status' => $transaksii->status,
            'kurir' => $transaksii->kurir,
        ]);
    }
    
    // public function gudang(Request $request, $id)
    // {
    //     $request->validate([
    //         'status' => 'required|string',
    //     ]);

    //     // Ambil data transaksi berdasarkan ID
    //     $transaksii = Transaksii::find($id);
    //     if (!$transaksii) {
    //         return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
    //     }

    //     // Ambil data kurir dari user yang login
    //     // $user = auth()->user();
    //     // $kurir = $user->kurir;

    //     // if (!$kurir) {
    //     //     return response()->json(['message' => 'Kurir belum terdaftar atau tidak punya relasi.'], 403);
    //     // }

    //     // Update data transaksi: waktu masuk gudang dan status
    //     // $transaksii->kurir_id = $kurir->kurir_id;
    //     $transaksii->waktu_digudang = now();
    //     $transaksii->status = $request->status;
    //     $transaksii->pernah_digudang = true;
    //     $transaksii->save();

    //     // Simpan data pengiriman dengan status gudang
    //     Pengiriman::create([
    //         // 'kurir_id' => $kurir->kurir_id,
    //         'transaksii_id' => $transaksii->id,
    //         'status' => 'gudang'
    //     ]);

    //     return response()->json([
    //         'message' => 'Barang telah masuk ke gudang.',
    //         'status' => $transaksii->status,
    //         // 'kurir' => $transaksii->kurir,
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
            case 'digudang':
                $transaksii->waktu_digudang = now();
                break;
                
                case 'diproses':
                    $transaksii->waktu_proses = now();
                    // $transaksii->pernah_digudang = true; // Pastikan nilainya tidak null
                    // Pengiriman::create([
                    //     'transaksii_id' => $transaksii->id,
                    //     'status' => 'gudang'
                    // ]);
                break;

            case 'tiba digudang':
                $transaksii->waktu_tiba = now();
                $transaksii->pernah_digudang = true;
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