<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengirimanRequest;
use App\Http\Requests\UpdatePengirimanRequest;
use App\Models\Kurir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Pengiriman;
use Illuminate\Support\Facades\Log;

class PengirimanController extends Controller
{

     // Menampilkan form tambah pengiriman
    //  public function create()
    //  {
    //      $kurir = Kurir::all(); // Mengambil semua data kurir
    //      return view('dashboard.pengiriman.create', compact('kurir'));
    //  }

    // Mengambil semua data pengiriman dengan pagination
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;
        DB::statement(query: 'set @no=0+' . $page * $per);
        // $pengiriman = Pengiriman::with('user')
        // Pengiriman::with('kurir.user');
       $data = Pengiriman::with('transaksii.pengguna.user')->with('kurir.user')->with('transaksii.asalProvinsi', 'transaksii.asalKota','transaksii.asalKecamatan', 'transaksii.tujuanProvinsi', 'transaksii.tujuanKota', 'transaksii.tujuanKecamatan' )// tambahkan ini
        ->when($request->search, function (Builder $query, string $search) {
            $query->where('kurir_id', 'like', "%$search%")
                ->orWhere('deskripsi', 'like', "%$search%")
                ->orWhere('transaksii_id', 'like', "%$search%");
        })
        ->when($request->status, function ($query, $status) {  
                Log::info($status); 
                $query->where('status', $status);
            })
            ->when(auth()->user()->role->name === 'kurir', function ($query) {
                Log::info('b');
                $query->where(function ($q) {
                    Log::info('e');
                    $kurirId = auth()->user()->kurir->kurir_id;
                    $q->whereNull('kurir_id')
                    ->orWhere('kurir_id', $kurirId);
                });
            })
        ->select('pengiriman.*')
        ->latest()->paginate($per);

            $no = ($data->currentPage()-1) * $per + 1;

            // ini kalau mau manggil colom accesor secara langsung no_resi tdk kayak gini transaksii.no_resi
            // ini dikeluarkan dari transaksii 
            foreach($data as $item){
                $item->no = $no++;
                // $item->no_resi = $item->transaksii->no_resi ?? null;    
            }


        return response()->json($data);
    }

    // Menampilkan detail pengiriman berdasarkan ID
    public function show(Pengiriman $pengiriman)
    {
        $pengiriman->load('transaksi'); // eager load relasi transaksi

        return response()->json([
            'success' => true,
            'pengiriman' => [
                'deskripsi' => $pengiriman->deskripsi,
                'kurir_id' => $pengiriman->kurir_id,
                'transaksii_id' => $pengiriman->transaksii_id,
                'transaksi' => $pengiriman->transaksi, // ← relasi transaksi
            ]
        ]);
    }


    // Menambahkan data pengiriman baru
    // public function store(StorePengirimanRequest $request)
    // {
    //     $pengiriman = Pengiriman::create($request->validated());

    //     return response()->json([
    //         'message' => 'Pengiriman berhasil ditambahkan',
    //         'data' => $pengiriman
    //     ], 201);
    // }

    public function store(StorePengirimanRequest $request)
    {
        $request->validate([
            'deskripsi' => 'required|string',
            'kurir_id' => 'nullable|exists:kurir,kurir_id',
            'transaksii_id' => 'required|exists:transaksii,id',
        ]);

        Pengiriman::create([
            'deskripsi' => $request->deskripsi,
            'kurir_id' => $request->kurir_id,
            'transaksii_id' => $request->transaksii_id,
        ]);
        return response()->json([
            'success' => 'Berhasil'
        ]);
    }

    // Mengupdate data pengiriman
    public function update(UpdatePengirimanRequest $request, $id)
    {
        $pengiriman = Pengiriman::find($id);

        if (!$pengiriman) {
            return response()->json(['message' => 'Pengiriman tidak ditemukan'], 404);
        }

        $pengiriman->update($request->validated());

        return response()->json([
            'message' => 'Pengiriman berhasil diperbarui',
            'data' => $pengiriman
        ]);
    }
    // public function get()
    // {
    //     return response()->json([
    //         'success' => true,
    //         'data' => Kurir::select('deskripsi', 'kurir_id', 'penerima', 'alamat', 'paket', 'status', 'tanggal_pengiriman', 'tanggal_penerimaan', 'biaya')->get()
    //     ]);
    // }
    public function get()
    {
        $kurir = Kurir::with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 'kurir'); // pastikan hanya user dengan role kurir
            })
            ->get()
            ->map(function ($k) {
                return [
                    // 'kurir_id' => $k->kurir_id,
                    'nama_kurir' => $k->user->name,
                ];
            });

        return response()->json([
            'success' => true,
            'kurir' => $kurir,
        ]);
    }

    // public function updateStatus(Request $request, $id)
    // {


    //     $pengiriman = Pengiriman::findOrFail($id);

    //     // Optional: pastikan yang update adalah kurir-nya
    //     if ($pengiriman->kurir_id !== auth()->id()) {
    //         return response()->json(['message' => 'Unauthorized'], 403);
    //     }

    //     $pengiriman->status = $request->status;
    //     $pengiriman->save();

    //     return response()->json(['message' => 'Status updated']);
    // }

    public function pengirimanKurir()
    {
        $pengiriman = Pengiriman::where('kurir_id', auth()->id())
            ->with('kurir.user', 'transaksii')
            ->paginate(10); // sesuaikan dengan paginate-mu

        return response()->json($pengiriman);
    }



    // Menghapus pengiriman
    public function destroy($id)
    {
        $pengiriman = Pengiriman::find($id);

        if (!$pengiriman) {
            return response()->json(['message' => 'Pengiriman tidak ditemukan'], 404);
        }

        $pengiriman->delete();

        return response()->json(['message' => 'Pengiriman berhasil dihapus']);
    }
}
