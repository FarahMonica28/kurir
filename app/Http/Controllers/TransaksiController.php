<?php

namespace App\Http\Controllers;

use App\Models\Kurir;
use App\Models\Transaksi;
use App\Models\Pengguna;
// use App\Models\User;
// use App\Models\Kurir;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;
    
        DB::statement('set @no=0+' . $page * $per);

        // $data = Transaksi::with('pengguna.user');// pastikan ada relasi ke kurir di model
        $data = Transaksi::with('kurir.user')->with('pengguna.user') // pastikan ada relasi ke kurir di model
            ->when($request->search, function ($query, $search) {
                $query->where('nama_barang', 'like', "%$search%")
                    ->orWhere('jarak', 'like', "%$search%")
                    // ->orWhere('pengirim', 'like', "%$search%")
                    ->orWhere('penerima', 'like', "%$search%")
                    ->orWhere('alamat_asal', 'like', "%$search%")
                    ->orWhere('alamat_tujuan', 'like', "%$search%")
                    ->orWhere('penilaian', 'like', "%$search%");
                    // ->orWhere('status', '!=', 'Terkirim')->get();
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->has('exclude_status'), function ($query) use ($request) {
                $query->where('status', '!=', $request->exclude_status);
                Log::info('a');
            })     
            // // Filter jika role pengguna (hanya bisa lihat order sendiri)
            // ->when(auth()->user()->role === 'pengguna', function ($query) {
            //     $query->where('pengguna_id', auth()->id());
            // })
            ->when(auth()->user()->role->name === 'kurir', function ($query) {
                Log::info('b');
                $query->where(function ($q) {
                    Log::info('e');
                    $kurirId = auth()->user()->kurir->kurir_id;
                    $q->whereNull('kurir_id') // hanya yang belum diambil kurir
                      ->orWhere('kurir_id', $kurirId); // atau yang diambil oleh kurir tersebut
                });
            })
            ->when(auth()->user()->role->name === 'pengguna', function ($query) {
                Log::info('pengguna');
                $penggunaId = auth()->user()->pengguna->pengguna_id;
                // Log::info('Pengguna',$penggunaId);
                $query->Where('pengguna_id', $penggunaId); // atau yang diambil oleh kurir tersebut
                
            })
                     
            ->latest()
            ->paginate($per);

    
        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }
    
        return response()->json($data);
    }
    
    public function riwayat()
    {
        $user = auth()->user();
        if ($user->role === 'kurir') {
            $kurirId = $user->kurir->kurir_id;
            $transaksi = Transaksi::where('kurir_id', $kurirId)
                ->where('status', 'Terkirim')
                ->orderBy('waktu_terkirim', 'desc')
                ->get();
        } elseif ($user->role === 'pengguna') {
            $penggunaId = $user->pengguna->pengguna_id;
            $transaksi = Transaksi::where('pengguna_id', $penggunaId)
                ->where('status', 'Terkirim')
                ->orderBy('waktu_terkirim', 'desc')
                ->get();
        } else {
            $transaksi = collect(); // empty collection
        }
    
        return response()->json($transaksi);
    }
    

    public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:penjemputan barang,sedang dikirim,terkirim',
    ]);

    $transaksi = Transaksi::findOrFail($id);
    $transaksi->status = $request->status;
    $transaksi->save();

    // Ubah status kurir sesuai dengan status transaksi
    $kurir = $transaksi->kurir;

    if ($kurir) {
        if (in_array($transaksi->status, ['penjemputan barang', 'sedang dikirim'])) {
            $kurir->status = 'sedang menerima orderan';
        } elseif ($transaksi->status === 'terkirim') {
            $kurir->status = 'aktif';
        }

        $kurir->save();
    }

    return response()->json([
        'success' => true,
        'message' => 'Status transaksi berhasil diperbarui',
        'transaksi' => $transaksi,
        'kurir' => $kurir,
    ]);
}

    public function show(Transaksi $transaksi)
    {

        $transaksi->load('pengguna.user', 'kurir');
        // $transaksi->load('kurir');
        // $transaksi->load('pengguna');

        // $transaksi = Transaksi::with('kurir.user')->findOrFail($id);
        return response()->json( [ 

                // 'id' => $transaksi->id,
                'nama_barang' => $transaksi->nama_barang,
                'alamat_asal' => $transaksi->alamat_asal,
                'alamat_tujuan' => $transaksi->alamat_tujuan,
                'penerima' => $transaksi->penerima,
                // 'pengirim' => $transaksi->pengirim,
                'pengguna_id' => $transaksi->pengguna_id,
                'no_hp_penerima' => $transaksi->no_hp_penerima,
                'jarak' => $transaksi->jarak,
                'biaya' => $transaksi->biaya,
                'status' => $transaksi->status,
                'kurir' => $transaksi->kurir,
                'pengguna' => $transaksi->pengguna, // Tambahkan ini untuk frontend
                'penilaian' => $transaksi->penilaian, // Tambahkan ini untuk frontend
                'komentar' => $transaksi->komentar, // Tambahkan ini untuk frontend
        ]);
        
    }

    public function store(Request $request)
    {
        $transaksi = $request->validate([
            // 'pengirim' => 'required|string',
            // 'pengirim' => 'required|string',
            'penerima' => 'required|string',
            'no_hp_penerima' => 'required|string',
            'alamat_asal' => 'required|string',
            'alamat_tujuan' => 'required|string',
            'nama_barang' => 'required|string',
            'status' => 'required|string',
            'jarak' => ' nullable|numeric|min:0',
            'biaya' => 'nullable|numeric|min:0',
            // 'waktu' => 'nullable|date',
            'waktu' => 'nullable|date|before_or_equal:now',
            // 'penilaian' => 'nullable|string',
            // 'komentar' => 'nullable|string',
            'penilaian' => 'nullable|integer',
            'komentar' => 'nullable|string',
            'pengguna_id' => 'nullable|exists:pengguna,pengguna_id',
            // 'kurir_id' => 'nullable|exists:kurir,kurir_id',
            
        ]);

        $pengguna = Pengguna::where('user_id', $request->id)->first();; // pastikan ini nama field-nya
    if (!$pengguna) {
        return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
    }

    $kurir = Kurir::where('status', 'aktif')->first();

    if (!$kurir) {
        return response()->json(['message' => 'Tidak ada kurir aktif yang tersedia'], 422);
    }

        
        // $kurir = Kurir::where('status', 'aktif')->first();

        // if (!$kurir) {
        //     return response()->json(['message' => 'Tidak ada kurir aktif'], 422);
        // }

        // $transaksi = Transaksi::create([
        //     'kurir_id' => $kurir->kurir_id,
        //     'status' => 'menunggu penjemputan',
        //     // data lainnya...
        // ]);

        // // Kurir langsung ubah status jadi "sedang menerima orderan"
        // $kurir->status = 'sedang menerima orderan';
        // $kurir->save();


        // $transaksi = Transaksi::create($data);
        Transaksi::create([
            'nama_barang' => $request->nama_barang,
            'alamat_asal' => $request->alamat_asal,
            'alamat_tujuan' => $request->alamat_tujuan,
            // 'pengirim' => $request->pengirim,
            'penerima' => $request->penerima,
            'no_hp_penerima' => $request->no_hp_penerima,
            // 'status' => $request->status ?? 'Dalam Proses',
            'status' => $request->status,
            'jarak' => $request->jarak,   
            'biaya' => $request->biaya,
            'penilaian' => $request->penilaian,
            'komentar' => $request->komentar,
            // 'waktu' => now()->format('d-m-Y H:i:s'),
            'waktu' => now()->format('Y-m-d H:i:s'),
            // 'kurir_id' => $request->kurir_id,
            'pengguna_id' => $pengguna->pengguna_id
        ]);

        // Update status kurir jadi sedang menerima orderan
    $kurir->status = 'sedang menerima orderan';
    $kurir->save();
        return response()->json(['message' => 'Berhasil menambahkan transaksi', 'data' => $transaksi]);
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


    // public function update(Request $request, $id)
    // {
    //     $transaksi = Transaksi::findOrFail($id);
    //     // $validatedData = $request->validated();

    //     $data = $request->validate([
    //         // 'id' => 'required|integer|exists:transaksi,id', // <--- tambah ini
    //         // 'no_transaksi' => 'required|string|unique:transaksi,no_transaksi,' . $id,
    //         'pengirim' => 'required|string',
    //         'penerima' => 'required|string',
    //         'no_hp_penerima' => 'required|string',
    //         'alamat_asal' => 'required|string',
    //         'alamat_tujuan' => 'required|string',
    //         'nama_barang' => 'required|string',
    //     //     'jarak' => 'required|numeric',
    //     //     'biaya' => 'required|numeric',
    //     //     'waktu' => 'required|date',
    //         'status' => 'required|string',
    //     //     'penilaian' => 'nullable|integer',
    //     //     'komentar' => 'nullable|string',
    //     //     'kurir_id' => 'required|exists:kurir,kurir_id',
    //     ]);
    
    //     // $transaksi->update($data);
    //     // $transaksi->update([
        //     //     'status' => $request->status,
        //     //     'jarak' => $request->berat,
    //     //     'biaya' => $request->biaya,
    //     //     'waktu' => now(),
    //     // ]);
        

    //     return response()->json($transaksi);
    // }

    // TransaksiController.php
public function update(Request $request, $id)
{
    $request->validate([
        'status' => 'required|string',
        'jarak' => 'required|numeric|min:1',
        'biaya' => 'required|numeric|min:0',
    ]);

    $transaksi = Transaksi::where('id', $id)->firstOrFail();
    
    // Cegah kurir mengambil pesanan yang sudah diambil kurir lain
    $user = auth()->user();
    $kurirId = $user->kurir->kurir_id ?? null;
    $penggunaId = $user->pengguna->pengguna_id ?? null;

    // Cegah kurir mengambil pesanan yang sudah diambil kurir lain
    if ($user->role === 'kurir') {
        if ($transaksi->kurir_id && $transaksi->kurir_id != $kurirId) {
            return response()->json([
                'message' => 'Pesanan sudah diambil oleh kurir lain.'
            ], 403);
        }
    }
    if ($user->role === 'pengguna') {
        if ($transaksi->pengguna_id && $transaksi->pengguna_id != $penggunaId) {
            return response()->json([
                // 'message' => 'Pesanan sudah diambil oleh kurir lain.'
            ], 402);
        }
    }

    // Simpan waktu sesuai status
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
    
    $transaksi->update([
        'status' => $request->status,
        'jarak' => $request->jarak,
        'biaya' => $request->biaya,
        'kurir_id' => $request->kurir_id,
        // 'kurir_id' => $request->kurir->kurir_id,
        // 'waktu' => $waktuLama ? $waktuLama . '<br>' . $statusString : $statusString,
    ]);
    
    
    $transaksi->save();

    return response()->json([
        'message' => 'Status berhasil diperbarui',
        'status' => $transaksi->status,
        
    ]);
}

public function storePenilaian(Request $request)
{
    $request->validate([
        // 'id' => 'sometimes|integer|exists:id',
        // 'id' => 'required|exists:tb_transaksi,id',
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

    public function destroy($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->delete();

        return response()->json(['message' => 'Transaksi deleted']);
    }

    
}

