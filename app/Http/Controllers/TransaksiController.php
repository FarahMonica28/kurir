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
        // Ambil jumlah item per halaman dari request, default 10
        $per = $request->per ?? 10;
    
        // Ambil halaman saat ini, dikurangi 1 karena pagination dimulai dari 0
        $page = $request->page ? $request->page - 1 : 0;
    
        // Statement untuk membuat nomor urut (auto increment) di query SQL
        DB::statement('set @no=0+' . $page * $per);
    
        // Ambil data transaksi beserta relasi ke kurir.user dan pengguna.user
        $data = Transaksi::with('kurir.user')->with('pengguna.user')
    
            // Jika ada parameter `search`, filter berdasarkan beberapa kolom
            ->when($request->search, function ($query, $search) {
                $query->where('nama_barang', 'like', "%$search%")
                    ->orWhere('jarak', 'like', "%$search%")
                    // ->orWhere('pengirim', 'like', "%$search%") // bisa diaktifkan jika dibutuhkan
                    ->orWhere('penerima', 'like', "%$search%")
                    ->orWhere('alamat_asal', 'like', "%$search%")
                    ->orWhere('alamat_tujuan', 'like', "%$search%")
                    ->orWhere('penilaian', 'like', "%$search%");
            })
    
            // Filter berdasarkan status jika dikirimkan dari frontend
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
    
            // Jika ada exclude_status (misal exclude status "Selesai"), lakukan filter
            ->when($request->has('exclude_status'), function ($query) use ($request) {
                $query->where('status', '!=', $request->exclude_status);
                Log::info('a'); // log debug
            })
    
            // Role: kurir — tampilkan transaksi yang belum ada kurir_id atau milik kurir itu sendiri 
            ->when(auth()->user()->role->name === 'kurir', function ($query) {
                Log::info('b'); // log debug
                $query->where(function ($q) {
                    Log::info('e'); // log debug
                    $kurirId = auth()->user()->kurir->kurir_id;
                    $q->whereNull('kurir_id')
                      ->orWhere('kurir_id', $kurirId);
                });
            })
    
            // Role: kurir — pengecekan status kurir dan update otomatis status kurir berdasarkan order aktif
            ->when(auth()->user()->role->name === 'kurir', function ($query) {
                $user = auth()->user();
                $kurir = $user->kurir;
    
                // Jika tidak ada data kurir atau status kurir bukan aktif / sedang menerima orderan, tolak akses
                if (!$kurir || !in_array($kurir->status, ['aktif', 'sedang menerima orderan'])) {
                    abort(403, 'Kurir tidak aktif');
                }
    
                $kurirId = $kurir->kurir_id;
    
                // Cek apakah kurir sedang memiliki order dengan status aktif
                $hasOrder = Transaksi::where('kurir_id', $kurirId)
                    ->whereIn('status', ['Penjemputan Barang', 'Sedang Dikirim'])
                    ->exists();
    
                // Jika kurir sedang punya order, ubah status jadi "sedang menerima orderan"
                if ($hasOrder && $kurir->status === 'aktif') {
                    $kurir->update(['status' => 'sedang menerima orderan']);
                }
                // Jika tidak ada order aktif, kembalikan status jadi "aktif"
                elseif (!$hasOrder && $kurir->status === 'sedang menerima orderan') {
                    $kurir->update(['status' => 'aktif']);
                }
    
                // Filter ulang transaksi: hanya yang belum diambil kurir atau sudah diambil oleh kurir tersebut
                $query->where(function ($q) use ($kurirId) {
                    $q->whereNull('kurir_id')
                      ->orWhere('kurir_id', $kurirId);
                });
            })
    
            // Role: pengguna — hanya tampilkan transaksi milik pengguna yang sedang login
            ->when(auth()->user()->role->name === 'pengguna', function ($query) {
                Log::info('pengguna'); // log debug
                $penggunaId = auth()->user()->pengguna->pengguna_id;
                $query->where('pengguna_id', $penggunaId);
            })
    
            // Urutkan dari yang terbaru (created_at DESC)
            ->latest()
            
            // Paginate hasil query
            ->paginate($per);
    
        // Tambahkan nomor urut berdasarkan halaman
        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }
    
        // Return hasil dalam bentuk JSON
        return response()->json($data);
    }
    public function store(Request $request)
    {

         // Validasi input yang diterima dari request
        // Setiap field yang wajib diisi diberi aturan validasi
        $transaksi = $request->validate([
            // 'pengirim' => 'required|string',
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
            'pengguna_id' => 'nullable|exists:pengguna,pengguna_id',
            
        ]);

        // Mencari data pengguna berdasarkan user_id yang diterima dari request
        // Pastikan user_id ada dalam request dan sesuai dengan relasi pada model
        $pengguna = Pengguna::where('user_id', $request->id)->first();
        if (!$pengguna) {
            // Jika pengguna tidak ditemukan, kembalikan response error
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }

        // Mencari kurir yang sedang aktif
        $kurir = Kurir::where('status', 'aktif')->first();

        // Jika tidak ada kurir aktif, kembalikan response error
        if (!$kurir) {
            return response()->json(['message' => 'Tidak Ada Kurir Aktif yang Tersedia'], 422);
        }

        // Membuat transaksi baru dengan data yang diterima dari request
        Transaksi::create([
            'nama_barang' => $request->nama_barang,
            'alamat_asal' => $request->alamat_asal,
            'alamat_tujuan' => $request->alamat_tujuan,
            'penerima' => $request->penerima,
            'no_hp_penerima' => $request->no_hp_penerima,
            'status' => $request->status,
            'jarak' => $request->jarak,   
            'biaya' => $request->biaya,
            'penilaian' => $request->penilaian,
            'komentar' => $request->komentar,
            'waktu' => now()->format('Y-m-d H:i:s'), // waktu sekarang saat transaksi dibuat
            'pengguna_id' => $pengguna->pengguna_id // Mengaitkan transaksi dengan pengguna yang terkait
        ]);

        // Update status kurir menjadi 'sedang menerima orderan' setelah transaksi dibuat
        $kurir->status = 'sedang menerima orderan';
        $kurir->save();

        // Kembalikan response JSON yang berisi pesan sukses dan data transaksi
        return response()->json(['message' => 'Berhasil menambahkan transaksi', 'data' => $transaksi]);
    }
    
    public function show(Transaksi $transaksi)
    {

        $transaksi->load('pengguna.user', 'kurir');

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

    public function update(Request $request, $id)
    {
        // Validasi input yang diterima dari request
        // Memastikan bahwa status, jarak, dan biaya memiliki format yang benar
        $request->validate([
            'status' => 'required|string', // status wajib diisi dan harus berupa string
            'jarak' => 'required|numeric|min:1', // jarak wajib diisi, berupa angka, dan minimal 1
            'biaya' => 'required|numeric|min:0', // biaya wajib diisi, berupa angka, dan minimal 0
        ]);
    
        // Mencari transaksi berdasarkan ID yang diberikan
        // Jika transaksi tidak ditemukan, maka akan menghasilkan error 404
        $transaksi = Transaksi::where('id', $id)->firstOrFail();
        
        // Mendapatkan data pengguna yang sedang login
        $user = auth()->user();
    
        // Mendapatkan ID kurir dari data user, jika ada
        $kurirId = $user->kurir->kurir_id ?? null;
    
        // Mendapatkan ID pengguna dari data user, jika ada
        $penggunaId = $user->pengguna->pengguna_id ?? null;
    
        // Cegah kurir mengambil pesanan yang sudah diambil oleh kurir lain
        // Jika yang login adalah kurir, pastikan transaksi ini belum diambil oleh kurir lain
        if ($user->role === 'kurir') {
            // Jika transaksi sudah memiliki kurir_id yang berbeda, berarti pesanan sudah diambil oleh kurir lain
            if ($transaksi->kurir_id && $transaksi->kurir_id != $kurirId) {
                // Jika sudah diambil oleh kurir lain, maka return respons error
                return response()->json([
                    'message' => 'Pesanan sudah diambil oleh kurir lain.' // Pesanan tidak bisa diubah oleh kurir lain
                ], 403); // Mengembalikan status kode 403 (Forbidden)
            }
        }
    
        // Mengatur waktu berdasarkan status transaksi yang baru
        // Tergantung pada status yang diterima, waktu yang sesuai akan diset
        switch ($request->status) {
            case 'Penjemputan Barang':
                $transaksi->waktu_penjemputan = now(); // Set waktu penjemputan barang
                break;
            case 'Sedang Dikirim':
                $transaksi->waktu_proses = now(); // Set waktu proses pengiriman
                break;
            case 'Terkirim':
                $transaksi->waktu_terkirim = now(); // Set waktu barang terkirim
                break;
        }
    
        // Update penilaian dan komentar berdasarkan input yang diterima dari request
        $transaksi->penilaian = $request->penilaian;
        $transaksi->komentar = $request->komentar;
    
        // Mendapatkan waktu baru dengan format yang diinginkan untuk status transaksi
        $waktuBaru = now()->format('d-m-Y H:i:s');
    
        // Membuat string status yang berisi status transaksi dan waktu terbaru
        $statusString = $request->status . ' (' . $waktuBaru . ')';
    
        // Update transaksi dengan status baru, jarak, biaya, dan kurir_id (yang dipilih pada request)
        $transaksi->update([
            'status' => $request->status,
            'jarak' => $request->jarak,  // Mengupdate jarak
            'biaya' => $request->biaya,  // Mengupdate biaya
            'kurir_id' => $request->kurir_id, // Mengupdate kurir yang menangani transaksi
        ]);
    
        // Simpan perubahan transaksi yang sudah diupdate
        $transaksi->save();
    
        // Mengembalikan response sukses dengan status dan pesan konfirmasi
        return response()->json([
            'message' => 'Status berhasil diperbarui', // Pesan sukses
            'status' => $transaksi->status,  // Status terbaru transaksi
        ]);
    }
    
    
    public function updateStatus(Request $request, $id)
    {
        // Validasi input agar status wajib diisi dan hanya boleh salah satu dari 3 nilai berikut
        $request->validate([
            'status' => 'required|in:penjemputan barang,sedang dikirim,terkirim',
        ]);
    
        // Cari data transaksi berdasarkan ID, jika tidak ditemukan akan otomatis gagal (404)
        $transaksi = Transaksi::findOrFail($id);
    
        // Update status transaksi sesuai permintaan
        $transaksi->status = $request->status;
        $transaksi->save();
    
        // Ambil data kurir yang terkait dengan transaksi (melalui relasi)
        $kurir = $transaksi->kurir;
    
        // Jika transaksi punya kurir
        if ($kurir) {
            // Jika status transaksi masih dalam proses pengiriman
            if (in_array($transaksi->status, ['penjemputan barang', 'sedang dikirim'])) {
                // Ubah status kurir menjadi "sedang menerima orderan"
                $kurir->status = 'sedang menerima orderan';
            }
            // Jika status transaksi sudah selesai (terkirim)
            elseif ($transaksi->status === 'terkirim') {
                // Ubah status kurir menjadi "aktif" (siap ambil order baru)
                $kurir->status = 'aktif';
            }
    
            // Simpan perubahan pada data kurir
            $kurir->save();
        }
    
        // Kembalikan response JSON yang berisi data transaksi dan kurir
        return response()->json([
            'success' => true,
            'message' => 'Status transaksi berhasil diperbarui',
            'transaksi' => $transaksi,
            'kurir' => $kurir,
        ]);
    }
    
    public function storePenilaian(Request $request)
    {
        // Validasi input dari request
        // - 'id' bersifat nullable, harus berupa integer dan ada di tabel transaksi
        // - 'penilaian' wajib diisi dan harus berupa string
        // - 'komentar' bersifat nullable dan harus berupa string jika ada
        $request->validate([
            'id' => 'nullable|integer|exists:transaksi,id', // Validasi id transaksi, integer dan harus ada di database
            'penilaian' => 'required|string', // Penilaian wajib diisi dan berupa string
            'komentar' => 'nullable|string', // Komentar opsional, jika ada harus berupa string
        ]);
    
        // Mencari transaksi berdasarkan ID yang diterima dari request
        $transaksi = Transaksi::find($request->id);
        // Jika transaksi tidak ditemukan, mengembalikan response 404 dengan pesan error
        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }
    
        // Menyimpan penilaian dan komentar yang diterima dari request ke dalam transaksi
        $transaksi->penilaian = $request->penilaian;
        $transaksi->komentar = $request->komentar;
        
        // Menyimpan perubahan ke dalam database
        $transaksi->save();
    
        // Mengembalikan response sukses setelah penilaian berhasil disimpan
        return response()->json(['message' => 'Penilaian disimpan.']);
    }

    public function riwayat()
    {
        // Ambil user yang sedang login
        $user = auth()->user();
    
        // Jika user adalah kurir
        if ($user->role === 'kurir') {
            // Ambil ID kurir dari relasi user
            $kurirId = $user->kurir->kurir_id;
    
            // Ambil semua transaksi milik kurir ini yang sudah berstatus "Terkirim"
            // Diurutkan berdasarkan waktu pengiriman terbaru
            $transaksi = Transaksi::where('kurir_id', $kurirId)
                ->where('status', 'Terkirim')
                ->orderBy('waktu_terkirim', 'desc')
                ->get();
        }
    
        // Jika user adalah pengguna
        elseif ($user->role === 'pengguna') {
            // Ambil ID pengguna dari relasi user
            $penggunaId = $user->pengguna->pengguna_id;
    
            // Ambil semua transaksi milik pengguna ini yang sudah berstatus "Terkirim"
            // Diurutkan berdasarkan waktu pengiriman terbaru
            $transaksi = Transaksi::where('pengguna_id', $penggunaId)
                ->where('status', 'Terkirim')
                ->orderBy('waktu_terkirim', 'desc')
                ->get();
        }
    
        // Jika bukan kurir atau pengguna, kembalikan data kosong
        else {
            $transaksi = collect(); // empty collection
        }
    
        // Kembalikan hasil sebagai JSON response
        return response()->json($transaksi);
    }
    
    public function destroy($id)
    {
        // Mencari transaksi berdasarkan ID yang diterima sebagai parameter
        // Jika transaksi tidak ditemukan, akan otomatis memunculkan error 404 (Not Found)
        $transaksi = Transaksi::findOrFail($id);
    
        // Menghapus transaksi yang ditemukan
        $transaksi->delete();
    
        // Mengembalikan response sukses dengan pesan bahwa transaksi telah dihapus
        return response()->json(['message' => 'Transaksi deleted']);
    }    
   
}

