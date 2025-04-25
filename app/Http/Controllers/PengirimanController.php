<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengirimanRequest;
use App\Http\Requests\UpdatePengirimanRequest;
use App\Models\Kurir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Pengiriman;

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
        $data = Pengiriman::join('kurir','kurir.kurir_id', '=','pengiriman.kurir_id')->join('users','users.id','=','kurir.user_id')
            ->when($request->search, function (Builder $query, string $search) {
                $query->where('no_resi', 'like', "%$search%")
                    ->orwhere('kurir_id', 'like', "%$search%")
                    ->orwhere('paket', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%")
                    ->orWhere('tanggal_dibuat', 'like', "%$search%")
                    ->orWhere('tanggal_pengiriman', 'like', "%$search%")
                    ->orWhere('tanggal_penerimaan', 'like', "%$search%")
                    ->orWhere('penerima', 'like', "%$search%")
                    ->orWhere('alamat', 'like', "%$search%")
                    ->orWhere('biaya', 'like', "%$search%");
            })
            ->select('pengiriman.*', 'users.name as name')
            ->latest()->paginate($per);
            $no = ($data->currentPage()-1) * $per + 1;
        foreach($data as $item){
            $item->no = $no++;
        }




        return response()->json($data);
    }

    // Menampilkan detail pengiriman berdasarkan ID
    public function show(pengiriman $pengiriman)
    {
        // $pengiriman = Pengiriman::with('kurir')->find($id);

        // if (!$pengiriman) {
        //     return response()->json(['message' => 'Pengiriman tidak ditemukan'], 404);
        // }

        return response()->json([
            'success' => true,
            'pengiriman' => [
                'no_resi' => $pengiriman->no_resi,
                'kurir_id' => $pengiriman->kurir_id,
                'paket' => $pengiriman->paket,
                'status' => $pengiriman->status,
                'tanggal_dibuat' => $pengiriman->tanggal_dibuat,
                'tanggal_pengiriman' => $pengiriman->tanggal_pengiriman,
                'tanggal_penerimaan' => $pengiriman->tanggal_penerimaan,
                'penerima' => $pengiriman->penerima,
                'alamat' => $pengiriman->alamat,
                'biaya' => $pengiriman->biaya
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
            'no_resi' => 'required|unique:pengiriman',
            'kurir_id' => 'required|exists:kurir,kurir_id',
            'paket' => 'required|string|max:255',
            'status' => 'required|in:dikemas,dikirim,diterima',
            'tanggal_dibuat' => 'nullable|date',
            'tanggal_pengiriman' => 'nullable|date',
            'tanggal_penerimaan' => 'nullable|date',
            'penerima' => 'required|string|max:255',
            'alamat' => 'required|string',
            'biaya' => 'required|numeric|min:0'
        ]);

        Pengiriman::create([
            'no_resi' => $request->no_resi,
            'kurir_id' => $request->kurir_id,
            'paket' => $request->paket,
            'status' => $request->status, 
            'penerima' => $request->penerima,
            'alamat' => $request->alamat,
            'tanggal_dibuat' =>$request->tanggal_dibuat,
            'tanggal_penerimaan' =>$request->tanggal_penerimaan,
            'tanggal_pengiriman' =>$request->tanggal_pengiriman,
            'biaya'=>$request->biaya,
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
    //         'data' => Kurir::select('no_resi', 'kurir_id', 'penerima', 'alamat', 'paket', 'status', 'tanggal_pengiriman', 'tanggal_penerimaan', 'biaya')->get()
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

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Dikemas,Dikirim,Diterima',
        ]);

        $pengiriman = Pengiriman::findOrFail($id);

        // Optional: pastikan yang update adalah kurir-nya
        if ($pengiriman->kurir_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pengiriman->status = $request->status;
        $pengiriman->save();

        return response()->json(['message' => 'Status updated']);
    }

    public function pengirimanKurir()
    {
        $pengiriman = Pengiriman::where('kurir_id', auth()->id())
            ->with('kurir.user')
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
