<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('SET @no = 0 + ' . ($page * $per));

        $data = Kategori::with('Barang')
            ->when($request->search, function ( Builder $query, $search) {
                $query->where('nama', 'like', "%$search%");
            })
            ->latest()
            ->paginate($per);

        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }

        return response()->json($data);
    }

    public function get()
    {
        $data = Kategori::with('Barang')->latest()->get();
        return response()->json($data);
    }



    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        Kategori::create($request->only('nama'));

        return response()->json(['message' => 'Kategori berhasil ditambahkan']);
    }

    // public function show($id)
    // {
    //     $kategori = Kategori::findOrFail($id);
    //     return response()->json($kategori);
    // }
    // public function show($id)
    // {
    //     $kategori = Kategori::with('barang')->findOrFail($id);

    //     return response()->json([
    //         'kategori' => $kategori,
    //         'barang' => $kategori->barang
    //     ]);
    // }
    public function show($id)
    {
        $kategori = Kategori::with('barang')->findOrFail($id);

        return response()->json([
            'nama' => $kategori->nama,
            'barang' => $kategori->barang->map(function ($b) {
                return [
                    'id' => $b->id,
                    'nama' => $b->nama,
                    'price' => $b->harga_sewa,
                    'photo' => asset('storage/' . $b->photo)
                ];
            })
        ]);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $kategori = Kategori::findOrFail($id);
        $kategori->update($request->only('nama'));

        return response()->json(['message' => 'Kategori berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}
