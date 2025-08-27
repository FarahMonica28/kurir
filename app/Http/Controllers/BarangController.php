<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Storage;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('SET @no = 0 + ' . ($page * $per));

        $data = Barang::with('kategori') // asumsi relasi kategori sudah dibuat di model Barang
            ->when($request->search, function ($query, $search) {
                $query->where('nama', 'like', "%$search%")
                        ->orwhere('stok', 'like', "%$search%")
                        ->orwhere('harga', 'like', "%$search%")
                        ->orwhere('kategori_id', 'like', "%$search%")
                        ->orwhere('photo', 'like', "%$search%");
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
        // Validasi input request
        $validated = $request->validate([
            'nama'        => 'required|string|max:255',
            'stok'        => 'required|integer|min:0',
            'harga'  => 'required|numeric|min:0',
            'kategori_id' => 'required|exists:kategori,id',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

         // Simpan foto barang jika ada
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photo', 'public');
        }
        

        // Simpan data barang ke database
        $barang = Barang::create([
            'nama'        => $validated['nama'],
            'stok'        => $validated['stok'],
            'harga'       => $validated['harga'],
            'kategori_id' => $validated['kategori_id'],
            'photo'       => $validated['photo'] ?? null,
        ]);

        return response()->json([
            'message' => 'Barang berhasil ditambahkan',
            'barang'  => $barang
        ], 201);
    }

    public function get()
    {
        $data = Barang::with('Kategori')->latest()->get();
        return response()->json($data);
    }

    
    public function show($id)
    // public function show($id)
    {
        // $barang = Barang::findOrFail($id);
        // $barang->load('kategori'); // pastikan relasi 'kategori' sudah didefinisikan di model
        $barang = Barang::with('kategori')->findOrFail($id);

        return response()->json([
            'id'         => $barang->id,
            'nama'       => $barang->nama,
            'stok'       => $barang->stok,
            'harga'      => $barang->harga,
            // 'photo'      => $barang->photo,
            'image' => asset('storage/' . $barang->photo),
            'kategori'   => [
                // 'id'   => $barang->kategori->id ?? null,
                'nama' => $barang->kategori->nama ?? null,
            ],
            // 'created_at' => $barang->created_at,
            // 'updated_at' => $barang->updated_at,
        ]);
    }


    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);

        // Validasi data yang diubah
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'stok'        => 'sometimes|integer|min:0',
            'kategori_id' => 'sometimes|exists:kategori,id',
            'harga'       => 'sometimes|numeric|min:0',
            'photo'       => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        // Handle upload dan hapus foto lama jika ada file baru
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($barang->photo) {
                Storage::disk('public')->delete($barang->photo);
            }

            // Simpan foto baru
            $validated['photo'] = $request->file('photo')->store('barang', 'public');
        }

        // Update data barang
        $barang->update($validated);

        return response()->json([
            'message' => 'Barang berhasil diperbarui',
            'barang'  => $barang
        ]);
    }

    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();

        return response()->json(['message' => 'Barang dihapus']);
    }
}

