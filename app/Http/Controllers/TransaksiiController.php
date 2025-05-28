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


        $data = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota',])->with('pengguna')->with('kurir.user')
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

            // Role: pengguna — hanya tampilkan transaksii milik pengguna yang sedang login
            ->when(auth()->user()->role->name === 'pengguna', function ($query) {
                Log::info('pengguna'); // log debug
                $penggunaId = auth()->user()->id;
                Log::info("Pengguna ID : ", ["user" => $penggunaId]);
                $query->where('pengguna_id', $penggunaId);
            })
            ->when(auth()->user()->role->name === 'kurir', function ($query) {
                Log::info('b'); // log debug
                $query->where(function ($q) {
                    Log::info('e'); // log debug
                    $kurirId = auth()->user()->kurir->kurir_id;
                    $q->whereNull('kurir_id')
                      ->orWhere('kurir_id', $kurirId);
                });
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

    // Ambil data transaksiii berdasarkan id
    public function get($id)
    {
        $transaksiii = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota', 'pengguna'])
            ->findOrFail($id);

        return response()->json($transaksiii);
    }

    // Simpan atau update transaksiii
    public function store(Request $request)
    {
        $transaksiii = $request->validate([
            'penerima' => 'required|string',
            'no_hp_penerima' => 'required|string',
            'tujuan_provinsi_id' => 'required|exists:provinces,id',
            'tujuan_kota_id' => 'required|exists:cities,id',
            'alamat_tujuan' => 'required|string',

            'pengguna_id' => 'nullable|exists:pengguna,pengguna_id',
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
            'kurir_id' => 'nullable|exists:kurir,kurir_id'
        ]);
        
        // Optional: cari pengguna untuk validasi tambahan (jika perlu)
        $pengguna = Pengguna::where('user_id', $request->id)->first();
        if (!$pengguna) {
            // Jika pengguna tidak ditemukan, kembalikan response error
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }

        $kurir = auth()->user()->kurir;
        // Simpan transaksii baru
        Transaksii::create([
            'no_resi' => 'TRX-' . strtoupper(uniqid()),
            'nama_barang' => $request->nama_barang,
            'berat_barang' => $request->berat_barang, // pastikan field DB kamu memang 'berat_barat'
            'alamat_asal' => $request->alamat_asal,
            'alamat_tujuan' => $request->alamat_tujuan,
            'penerima' => $request->penerima,
            'no_hp_penerima' => $request->no_hp_penerima,
            'status' => $request->status,
            'ekspedisi' => $request->ekspedisi,
            'layanan' => $request->layanan,
            'biaya' => $request->biaya,
            'penilaian' => $request->penilaian,
            'komentar' => $request->komentar,
            'waktu' => now()->format('Y-m-d H:i:s'),
            // 'pengguna_id' => $pengguna->pengguna_id,
            'pengguna_id' => auth()->id(), // atau $request->pengguna_id YANG VALID
            'asal_provinsi_id' => $request->asal_provinsi_id,
            'asal_kota_id' => $request->asal_kota_id,
            'tujuan_provinsi_id' => $request->tujuan_provinsi_id,
            'tujuan_kota_id' => $request->tujuan_kota_id,
            'kurir_id' => $kurir->kurir_id,
        ]);

        return response()->json([
            'message' => 'Berhasil menambahkan transaksii',
            'data' => $transaksiii
        ]);
    }

        public function update(Request $request, $id)
    {
        // Validasi input yang diterima dari request
        // Memastikan bahwa status, jarak, dan biaya memiliki format yang benar
        $request->validate([
            'status' => 'required|string', // status wajib diisi dan harus berupa string
        ]);
    
        // Mencari transaksii berdasarkan ID yang diberikan
        // Jika transaksii tidak ditemukan, maka akan menghasilkan error 404
        $transaksii = Transaksii::where('id', $id)->firstOrFail();
        
        // Mendapatkan data pengguna yang sedang login
        $user = auth()->user();

        // Mendapatkan ID pengguna dari data user, jika ada
        $penggunaId = $user->pengguna->pengguna_id ?? null;
    
        // Mengatur waktu berdasarkan status transaksii yang baru
        // Tergantung pada status yang diterima, waktu yang sesuai akan diset
        switch ($request->status) {
            case 'diproses':
                $transaksii->waktu_proses = now(); // Set waktu penjemputan barang
                break;
            case 'diambil kurir':
                $transaksii->waktu_diambil = now(); // Set waktu penjemputan barang
                break;
            case 'digudang':
                $transaksii->waktu_digudang = now(); // Set waktu penjemputan barang
                break;
            case 'dikirim':
                $transaksii->waktu_kirim = now(); // Set waktu proses pengiriman
                break;
            case 'selesai':
                $transaksii->waktu_selesai = now(); // Set waktu barang terkirim
                break;
        }
    
        // Update penilaian dan komentar berdasarkan input yang diterima dari request
        $transaksii->penilaian = $request->penilaian;
        $transaksii->komentar = $request->komentar;
    
        // Mendapatkan waktu baru dengan format yang diinginkan untuk status transaksii
        $waktuBaru = now()->format('d-m-Y H:i:s');
    
        // Membuat string status yang berisi status transaksii dan waktu terbaru
        $statusString = $request->status . ' (' . $waktuBaru . ')';
    
        // Update transaksii dengan status baru, jarak, biaya, dan kurir_id (yang dipilih pada request)
        $transaksii->update([
            'status' => $request->status,
        ]);
    
        // Simpan perubahan transaksii yang sudah diupdate
        $transaksii->save();
    
        // Mengembalikan response sukses dengan status dan pesan konfirmasi
        return response()->json([
            'message' => 'Status berhasil diperbarui', // Pesan sukses
            'status' => $transaksii->status,  // Status terbaru transaksii
        ]);
    }
    
    
    // TransaksiController.php
    public function antar(Request $request, $id)
    {
        $transaksii = Transaksii::findOrFail($id);

        // Simpan kurir_id & status baru
        $transaksii->update([
            'status' => $request->status,
            'kurir_id' => $request->kurir_id, // pastikan ini dikirim dari frontend
        ]); 

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

    public function ubahStatus(Request $request, $id)
    {
        $transaksi = Transaksii::findOrFail($id);
        $transaksi->status = $request->status;
        $transaksi->save();

        return response()->json(['message' => 'Status berhasil diubah']);
    }



}
