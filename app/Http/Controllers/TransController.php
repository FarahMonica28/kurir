<?php

namespace App\Http\Controllers;

use App\Models\Kurir;
use App\Models\Transaksi;
// use App\Models\User;
// use App\Models\Kurir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransController extends Controller
{

    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;
    
        DB::statement('set @no=0+' . $page * $per);
    
        $data = Transaksi::with('kurir.user') // pastikan ada relasi ke kurir di model
            ->when($request->search, function ($query, $search) {
                $query->where('nama_barang', 'like', "%$search%")
                    ->orWhere('jarak', 'like', "%$search%")
                    // ->orWhere('pengirim', 'like', "%$search%")
                    ->orWhere('pengguna_id', 'like', "%$search%")
                    ->orWhere('penerima', 'like', "%$search%")
                    ->orWhere('alamat_asal', 'like', "%$search%")
                    ->orWhere('alamat_tujuan', 'like', "%$search%")
                    ->orWhere('penilaian', 'like', "%$search%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->has('exclude_status'), function ($query) use ($request) {
                $query->where('status', '!=', $request->exclude_status);
            })
            ->when(auth()->user()->role->name === 'kurir', function ($query) {
                $query->where(function ($q) {
                    $kurirId = auth()->user()->kurir->kurir_id;
                    $q->whereNull('kurir_id') // hanya yang belum diambil kurir
                      ->orWhere('kurir_id', $kurirId); // atau yang diambil oleh kurir tersebut
                });
            })
            ->when(auth()->user()->role === 'pengguna', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->paginate($per);

    
        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }
    
        return response()->json($data);
    }
    

    
    public function store(Request $request)
    {
        $transaksi = $request->validate([
            // 'pengirim' => 'required|string',
            'pengguna_id' => 'nullable|exists:pengguna,pengguna_id',
            'penerima' => 'required|string',
            'no_hp_penerima' => 'required|string',
            'alamat_asal' => 'required|string',
            'alamat_tujuan' => 'required|string',
            'nama_barang' => 'required|string',
            'status' => 'required|string',
            'jarak' => ' nullable|numeric|min:0',
            'biaya' => 'nullable|numeric|min:0',
            'waktu' => 'nullable|date|before_or_equal:now',
            'penilaian' => 'nullable|integer',
            'komentar' => 'nullable|string',
            'kurir_id' => 'nullable|exists:kurir,kurir_id',
        ]);

         // Cari kurir aktif
        $kurir = Kurir::where('status', 'aktif')->first();

        if (!$kurir) {
            return response()->json(['message' => 'Tidak ada kurir aktif'], 422);
        }
        $kurir = auth()->user()->kurir;

        // Cek apakah kurir sudah pegang 1 order yang belum selesai
        $masihAktif = Transaksi::where('kurir_id', $kurir->kurir_id)
            ->whereIn('status', ['Penjemputan Barang', 'Sedang Dikirim']) // sesuaikan dengan status aktifmu
            ->exists();

            if ($masihAktif) {
                return response()->json([
                'message' => 'Kurir hanya bisa mengambil 1 order dalam satu waktu.'
            ], 403);
        }

        // $transaksi = Transaksi::create($data);
        $transaksi = Transaksi::create([
            // 'pengirim' => $transaksi['pengirim'],
            'pengguna_id' => $transaksi['pengguna_id'],
            'penerima' => $transaksi['penerima'],
            'no_hp_penerima' => $transaksi['no_hp_penerima'],
            'alamat_asal' => $transaksi['alamat_asal'],
            'alamat_tujuan' => $transaksi['alamat_tujuan'],
            'nama_barang' => $transaksi['nama_barang'],
            'status' => 'Penjemputan Barang', // status default saat dibuat
            'jarak' => $transaksi['jarak'] ?? null,
            'biaya' => $transaksi['biaya'] ?? null,
            'penilaian' => $transaksi['penilaian'] ?? null,
            'komentar' => $transaksi['komentar'] ?? null,
            'waktu' => now(), // waktu sekarang
            'kurir_id' => $kurir->kurir_id,
        ]);
        
        // Ubah status kurir menjadi "sedang menerima orderan"
        $kurir->status = 'sedang menerima orderan';
        $kurir->save();
        
        return response()->json([
            'message' => 'Berhasil menambahkan transaksi',
            'data' => $transaksi,
            'kurir' => $kurir
        ]);
        // return response()->json($transaksi, 201);
        // return response()->json(['message' => 'Berhasil menambahkan transaksi', 'data' => $transaksi]);
    }
    
    public function show(Transaksi $transaksi)
    {
        // $transaksi = Transaksi::with('kurir')->where('kurir_id', $id)->first();
        $transaksi->load('kurir.user');
        Log::info('Transaksi');
        return response()->json( [ 

                // 'id' => $transaksi->id,
                'nama_barang' => $transaksi->nama_barang,
                'alamat_asal' => $transaksi->alamat_asal,
                'alamat_tujuan' => $transaksi->alamat_tujuan,
                'penerima' => $transaksi->penerima,
                'pengguna_id' => $transaksi->pengguna_id,
                // 'pengirim' => $transaksi->pengirim,
                'no_hp_penerima' => $transaksi->no_hp_penerima,
                'jarak' => $transaksi->jarak, 
                'biaya' => $transaksi->biaya,
                'status' => $transaksi->status,
                'kurir' => $transaksi->kurir, // Tambahkan ini untuk frontend
                'penilaian' => $transaksi->penilaian, // Tambahkan ini untuk frontend
                'komentar' => $transaksi->komentar, // Tambahkan ini untuk frontend
        ]);
        
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'jarak' => 'required|numeric|min:1',
            'biaya' => 'required|numeric|min:0',
        ]);
        $transaksi = Transaksi::findOrFail($id);

        $transaksi->update($request->all());

        // Ambil kurir terkait
        $kurir = $transaksi->kurir;

        // Jika status diubah menjadi 'Penjemputan Barang', ubah status kurir
        if ($request->status === 'Penjemputan Barang' && $kurir) {
            $kurir->update(['status' => 'sedang menerima orderan']);
        }

        // Jika status diubah menjadi 'Terkirim', ubah status kurir kembali ke 'aktif'
        if ($request->status === 'Terkirim' && $kurir) {
            $kurir->update(['status' => 'aktif']);
        }

        $user = auth()->user();
        $kurirId = $user->kurir->kurir_id ?? null;

        // Cegah kurir mengambil pesanan yang sudah diambil kurir lain
        if ($user->role === 'kurir') {
            if ($transaksi->kurir_id && $transaksi->kurir_id != $kurirId) {
                return response()->json([
                    'message' => 'Pesanan sudah diambil oleh kurir lain.'
                ], 403);
            }
        }
        
        // Update kolom waktu dengan status baru dan timestamp
        // $waktuLama = $transaksi->waktu ?? '';
        $transaksi->penilaian = $request->penilaian;
        $transaksi->komentar = $request->komentar;
        
        $waktuBaru = now()->format('d-m-Y H:i:s');
        $statusString = $request->status . ' (' . $waktuBaru . ')';
        switch ($request->status) {
            case 'Penjemputan Barang':
                $transaksi->waktu_penjemputan = now();
                break;
            case 'Sedang Dikirim':
                $transaksi->waktu_proses = now();
                break;
            case 'Terkirim':
                $transaksi->waktu_terkirim = now();
                break;
            }
            
        $transaksi->save();
                    
        $transaksi->update([
            'status' => $request->status,
            'jarak' => $request->jarak,
            'biaya' => $request->biaya,
            'kurir_id' => $request->kurir_id,
            // 'waktu' => $waktuLama ? $waktuLama . '<br>' . $statusString : $statusString,
        ]);
                    
        return response()->json([
            'message' => 'Status berhasil diperbarui',
            'status' => $request->status,
        ]);
    }   
    

    public function storePenilaian(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer|exists:transaksi,id', 
            'penilaian' => 'required|string',
            'komentar' => 'nullable|string',
        ]);

        $transaksi = Transaksi::find($request->id);
        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }
        
        $transaksi->penilaian = $request->penilaian;
        $transaksi->komentar = $request->komentar;
        $transaksi->save();
        
        return response()->json(['message' => 'Penilaian disimpan.']);
    }

    public function updatePenilaian(Request $request, $id)
    {
        $request->validate([
            'penilaian' => 'nullable|numeric|min:1|max:5',
            'komentar' => 'nullable|string',
        ]);

        $transaksi = Transaksi::findOrFail($id);
        $transaksi->penilaian = $request->penilaian;
        $transaksi->komentar = $request->komentar;
        $transaksi->save();
        
        return response()->json(['message' => 'Penilaian disimpan.']);
    }

    
    public function destroy($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->delete();

        return response()->json(['message' => 'Transaksi deleted']);
    }

    
}

