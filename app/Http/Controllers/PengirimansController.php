<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengirimans;
use Illuminate\Http\Request;
use App\Models\Pengiriman;

class PengirimansController extends Controller
{
    // Ambil Barang
    public function ambilBarang(Request $request)
    {
        $request->validate([
            'no_resi' => 'required|string'
        ]);

        $pengiriman = Pengirimans::where('no_resi', $request->no_resi)->first();

        if (!$pengiriman) {
            return response()->json(['message' => 'Pengiriman tidak ditemukan'], 404);
        }

        if ($pengiriman->status !== 'belum_diambil') {
            return response()->json(['message' => 'Barang sudah diambil sebelumnya'], 400);
        }

        $pengiriman->status = 'diambil';
        $pengiriman->waktu_ambil = now();
        $pengiriman->save();

        return response()->json(['message' => 'Barang berhasil diambil']);
    }

    // Mulai Kirim
    public function mulaiKirim(Request $request)
    {
        $request->validate([
            'no_resi' => 'required|string'
        ]);

        $pengiriman = Pengiriman::where('no_resi', $request->no_resi)->first();

        if (!$pengiriman) {
            return response()->json(['message' => 'Pengiriman tidak ditemukan'], 404);
        }

        if ($pengiriman->status !== 'diambil') {
            return response()->json(['message' => 'Barang belum diambil dari gudang'], 400);
        }

        $pengiriman->status = 'sedang_dikirim';
        $pengiriman->save();

        return response()->json(['message' => 'Pengiriman dimulai']);
    }

    // Selesai Kirim
    public function selesaiKirim(Request $request)
    {
        $request->validate([
            'no_resi' => 'required|string'
        ]);

        $pengiriman = Pengiriman::where('no_resi', $request->no_resi)->first();

        if (!$pengiriman) {
            return response()->json(['message' => 'Pengiriman tidak ditemukan'], 404);
        }

        if ($pengiriman->status !== 'sedang_dikirim') {
            return response()->json(['message' => 'Pengiriman belum dimulai'], 400);
        }

        $pengiriman->status = 'terkirim';
        $pengiriman->waktu_kirim = now();
        $pengiriman->save();

        return response()->json(['message' => 'Pengiriman selesai']);
    }

    // Laporkan Masalah
    public function laporkanMasalah(Request $request)
    {
        $request->validate([
            'no_resi' => 'required|exists:pengirimans,no_resi',
            'masalah' => 'required|string|max:1000',
        ]);

        $pengiriman = Pengiriman::where('no_resi', $request->no_resi)->first();
        $pengiriman->update([
            'masalah' => $request->masalah,
            'waktu_masalah' => now(),
            'status' => 'gagal'
        ]);

        return response()->json([
            'message' => 'Masalah berhasil dilaporkan.',
            'data' => $pengiriman
        ]);
    }

    // Index - Menampilkan Daftar Pengiriman
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        $data = Pengirimans::when($request->search, function ($query, $search) {
            return $query->where('no_resi', 'like', "%$search%")
                ->orWhere('status', 'like', "%$search%")
                ->orWhere('penerima', 'like', "%$search%");
        })
        ->paginate($per);

        return response()->json($data);
    }

    // Store - Menambah Data Pengiriman Baru
    public function store(Request $request)
    {
        $request->validate([
            'no_resi' => 'required|string|unique:pengirimans,no_resi',
            'paket' => 'required|string',
            'penerima' => 'required|string',
            'alamat_tujuan' => 'required|string',
            'status' => 'required|string',
        ]);

        $pengiriman = Pengirimans::create([
            'no_resi' => $request->no_resi,
            'paket' => $request->paket,
            'penerima' => $request->penerima,
            'alamat_tujuan' => $request->alamat_tujuan,
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Pengiriman berhasil ditambahkan', 'data' => $pengiriman]);
    }

    // Update - Mengupdate Data Pengiriman
    public function update(Request $request, $id)
    {
        $request->validate([
            'no_resi' => 'required|string',
            'paket' => 'required|string',
            'penerima' => 'required|string',
            'alamat_tujuan' => 'required|string',
            'status' => 'required|string',
        ]);

        $pengiriman = Pengirimans::findOrFail($id);

        $pengiriman->update([
            'no_resi' => $request->no_resi,
            'paket' => $request->paket,
            'penerima' => $request->penerima,
            'alamat_tujuan' => $request->alamat_tujuan,
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Pengiriman berhasil diupdate', 'data' => $pengiriman]);
    }

    // Get - Menampilkan Data Pengiriman Berdasarkan ID
    public function get($id)
    {
        $pengiriman = Pengirimans::findOrFail($id);

        return response()->json(['data' => $pengiriman]);
    }

    // Update Status - Mengupdate Status Pengiriman
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:belum_diambil,diambil,sedang_dikirim,terkirim,gagal'
        ]);

        $pengiriman = Pengirimans::findOrFail($id);

        $pengiriman->status = $request->status;
        $pengiriman->save();

        return response()->json(['message' => 'Status pengiriman berhasil diupdate', 'data' => $pengiriman]);
    }

    // Destroy - Menghapus Pengiriman
    public function destroy($id)
    {
        $pengiriman = Pengirimans::findOrFail($id);
        $pengiriman->delete();

        return response()->json(['message' => 'Pengiriman berhasil dihapus']);
    }

    public function mulai($id)
    {
        $pengirimans = Pengirimans::findOrFail($id);
        $pengirimans->status = 'Sedang Dikirim';
        $pengirimans->save();

        return response()->json(['message' => 'Pengiriman dimulai']);
    }

    public function selesai($id)
    {
        $pengirimans = Pengirimans::findOrFail($id);
        $pengirimans->status = 'Selesai';
        $pengirimans->selesai_kirim_at = now();
        $pengirimans->save();

        return response()->json(['message' => 'Pengiriman selesai']);
    }

}
