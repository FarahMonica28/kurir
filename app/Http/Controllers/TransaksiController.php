<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
// use App\Models\User;
// use App\Models\Kurir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;
    
        DB::statement('set @no=0+' . $page * $per);
    
        $data = Transaksi::with('kurir.user') // pastikan ada relasi ke kurir di model
            ->when($request->search, function ($query, $search) {
                $query->where('nama_barang', 'like', "%$search%")
                    ->orWhere('berat_barang', 'like', "%$search%")
                    ->orWhere('pengirim', 'like', "%$search%")
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
            })
                     
            // ->when($request->kurir_id, function ($query, $kurirId) {
            //     $query->where('kurir_id', $kurirId);
            // })
            ->when(auth()->user()->role === 'kurir', function ($query) {
                $query->where('kurir_id', auth()->user()->kurir->kurir_id);
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
        // $transaksi = Transaksi::where('status', 'Terkirim')->get();
        // $transaksi = Transaksi::with('kurir')->where('status', 'Terkirim')->get();
        $transaksi = Transaksi::with('kurir.user')->where('status', 'Terkirim')->get();


        return response()->json($transaksi);
    }

    public function show(Transaksi $transaksi)
    {
        // $transaksi = Transaksi::with('kurir')->where('kurir_id', $id)->first();
        $transaksi->load('kurir');

        // $transaksi = Transaksi::with('kurir.user')->findOrFail($id);
        return response()->json( [ 

                // 'id' => $transaksi->id,
                'nama_barang' => $transaksi->nama_barang,
                'alamat_asal' => $transaksi->alamat_asal,
                'alamat_tujuan' => $transaksi->alamat_tujuan,
                'penerima' => $transaksi->penerima,
                'pengirim' => $transaksi->pengirim,
                'no_hp_penerima' => $transaksi->no_hp_penerima,
                'berat_barang' => $transaksi->berat_barang,
                'biaya' => $transaksi->biaya,
                'status' => $transaksi->status,
                'kurir' => $transaksi->kurir, // Tambahkan ini untuk frontend
                'penilaian' => $transaksi->penilaian, // Tambahkan ini untuk frontend
                'komentar' => $transaksi->komentar, // Tambahkan ini untuk frontend
                // 'kurir_id' => $transaksi->kurir->kurir_id,
                // 'transaksi' => [
                //     'status' => $transaksi->status,
                // ]
        ]);
        
    }

    public function store(Request $request)
    {
        $transaksi = $request->validate([
            // 'id' => 'required|integer|exists:transaksi,id', 
            // 'no_transaksi' => 'required|string|unique:transaksi,no_transaksi',
            'pengirim' => 'required|string',
            'penerima' => 'required|string',
            'no_hp_penerima' => 'required|string',
            'alamat_asal' => 'required|string',
            'alamat_tujuan' => 'required|string',
            'nama_barang' => 'required|string',
            'status' => 'required|string',
            'berat_barang' => ' nullable|numeric|min:0',
            'biaya' => 'nullable|numeric|min:0',
            // 'waktu' => 'nullable|date',
            'waktu' => 'nullable|date|before_or_equal:now',
            // 'penilaian' => 'nullable|string',
            // 'komentar' => 'nullable|string',
            'penilaian' => 'nullable|integer',
            'komentar' => 'nullable|string',
            'kurir_id' => 'nullable|exists:kurir,kurir_id',
        ]);

        // $transaksi = Transaksi::create($data);
        Transaksi::create([
            'nama_barang' => $request->nama_barang,
            'alamat_asal' => $request->alamat_asal,
            'alamat_tujuan' => $request->alamat_tujuan,
            'pengirim' => $request->pengirim,
            'penerima' => $request->penerima,
            'no_hp_penerima' => $request->no_hp_penerima,
            // 'status' => $request->status ?? 'Dalam Proses',
            'status' => $request->status,
            'berat_barang' => $request->berat_barang,   
            'biaya' => $request->biaya,
            'penilaian' => $request->penilaian,
            'komentar' => $request->komentar,
            // 'waktu' => now()->format('d-m-Y H:i:s'),
            'waktu' => now()->format('Y-m-d H:i:s'),
            'kurir_id' => $request->kurir_id,
        ]);

        // return response()->json($transaksi, 201);
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
    //     //     'berat_barang' => 'required|numeric',
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
        //     //     'berat_barang' => $request->berat,
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
        'berat_barang' => 'required|numeric|min:1',
        'biaya' => 'required|numeric|min:0',
    ]);

    $transaksi = Transaksi::where('id', $id)->firstOrFail();

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
        'berat_barang' => $request->berat_barang,
        'biaya' => $request->biaya,
        'kurir_id' => $request->kurir_id,
        // 'kurir_id' => $request->kurir->kurir_id,
        // 'waktu' => $waktuLama ? $waktuLama . '<br>' . $statusString : $statusString,
        ]);

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

