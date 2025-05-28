<?php

namespace App\Http\Controllers;

use App\Models\Transaksii;
use DB;
use LOG;
use Illuminate\Http\Request;

class GudangController extends Controller
{
         public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        // DB::statement('set @no=0+' . ($page - 1) * $per);
        DB::statement('set @no=0+' . $page * $per);


        $data = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota',])->with('pengguna')
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
            // Role: pengguna — hanya tampilkan transaksii milik pengguna yang sedang login
            ->when(auth()->user()->role->name === 'pengguna', function ($query) {
                Log::info('pengguna'); // log debug
                $penggunaId = auth()->user()->id;
                Log::info("Pengguna ID : ", ["user" => $penggunaId]);
                $query->where('pengguna_id', $penggunaId);
            })

        // $query->orderBy('created_at', 'desc');

        // $data = $query->paginate($per);
        // Urutkan dari yang terbaru (created_at DESC)
            ->latest()
            
            // Paginate hasil query
            ->paginate($per);

        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }

        return response()->json($data);
    }
}