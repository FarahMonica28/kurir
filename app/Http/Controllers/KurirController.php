<?php

namespace App\Http\Controllers;

use App\Models\Kurir;
use App\Http\Requests\StoreKurirRequest;
use App\Http\Requests\UpdateKurirRequest;
use App\Models\Pengiriman;
use App\Models\Transaksi;
use App\Models\Transaksii;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use function Laravel\Prompts\select;

class KurirController extends Controller
{
    /**
     * Get paginated list of kurir
     */
    public function index(Request $request)
    {
        // Ambil parameter `per` dari request (jumlah data per halaman), default 10 jika tidak ada
        $per = $request->per ?? 10;

        // Ambil parameter `page`, dan kurangi 1 (karena penomoran offset dimulai dari 0), default halaman 1 jika tidak ada
        $page = $request->page ? $request->page - 1 : 0;

        // Membuat variabel SQL @no untuk penomoran otomatis di MySQL (untuk memberi nomor urut di hasil query)
        DB::statement('set @no=0+' . $page * $per);

        // Query untuk mengambil data kurir:
        $data = Kurir::with('user') // Ambil juga relasi ke tabel `user` (relasi user() harus sudah didefinisikan di model Kurir)
            ->withCount('transaksii') // Hitung jumlah transaksi yang dimiliki tiap kurir, hasilnya jadi `transaksis_count`
            ->select('kurir_id', 'user_id', 'status', 'rating') // Hanya ambil kolom tertentu dari tabel `kurirs`
            
            // Jika ada parameter `search`, filter data berdasarkan kurir_id, status, atau rating yang mirip dengan kata kunci pencarian
            ->when($request->search, function ($query, $search) {
                $query->where('kurir_id', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%")
                    ->orWhere('rating', 'like', "%$search%");
            })
            ->latest() // Urutkan data dari yang terbaru (berdasarkan kolom created_at)
            ->paginate($per); // Paginate hasilnya sesuai jumlah per halaman

        // Hitung nomor awal berdasarkan halaman aktif
        $no = ($data->currentPage() - 1) * $per + 1;

        // Loop data untuk menambahkan properti no dan jumlah_transaksi ke tiap item
        foreach($data as $item){
            $item->no = $no++; // Nomor urut global (misalnya 11, 12, 13 untuk halaman 2)
            $item->jumlah_transaksii = $item->transaksii_count; // Salin nilai dari hasil withCount ke properti yang lebih mudah digunakan
        }

        // Kembalikan hasil data dalam format JSON
        return response()->json($data);
    }



    public function transaksiiCount()
    {
        // Ambil user yang sedang login (dari auth)
        $user = auth()->user();
    
        // Ambil data kurir yang terkait dengan user (relasi user->kurir)
        $kurir = $user->kurir;
    
        // Jika user bukan kurir (tidak punya relasi kurir), kembalikan semua jumlah 0
        if (!$kurir) {
            return response()->json([
                'today' => 0,
                'yesterday' => 0,
                'month' => 0,
            ]);
        }
    
        // Simpan kurir_id untuk digunakan di query berikutnya
        $kurirId = $kurir->kurir_id;
    
        // Hitung jumlah transaksi yang berhasil dikirim hari ini oleh kurir ini
        $todayCount = Transaksii::where('kurir_id', $kurirId)
            ->where('status', 'selesai') // Hanya hitung yang statusnya "terkirim"
            ->whereDate('waktu_selesai', Carbon::today()) // Dan waktu_terkirim adalah hari ini
            ->count();
    
        // Hitung jumlah transaksi yang berhasil dikirim kemarin
        $yesterdayCount = Transaksii::where('kurir_id', $kurirId)
            ->where('status', 'selesai')
            ->whereDate('waktu_selesai', Carbon::yesterday()) // Dan waktu_selesai adalah kemarin
            ->count();
    
        // Hitung jumlah transaksi yang berhasil dikirim dalam bulan ini
        $monthCount = Transaksii::where('kurir_id', $kurirId)
            ->where('status', 'selesai')
            ->whereMonth('waktu_selesai', Carbon::now()->month) // Bulan sekarang
            ->whereYear('waktu_selesai', Carbon::now()->year)   // Tahun sekarang
            ->count();
    
        // Kembalikan hasil hitungan dalam bentuk JSON
        return response()->json([
            'today' => $todayCount,
            'yesterday' => $yesterdayCount,
            'month' => $monthCount,
        ]);
    }
    


    public function transaksiiList(Request $request)
    {
        // Ambil user yang sedang login
        $user = auth()->user();

        // Validasi bahwa user yang login harus punya role 'kurir'
        if ($user->role->name !== 'kurir') {
            // Jika bukan kurir, kembalikan error 403 (akses ditolak)
            return response()->json([
                'success' => true,
            ], 403);
        }

        // Ambil data kurir yang terkait dengan user
        $kurir = $user->kurir;

        // Jika user tidak memiliki data kurir, kembalikan response kosong
        if (!$kurir) {
            return response()->json([
                'success' => true, 
                'data' => [],
                'todayCount' => 0,
                'yesterdayCount' => 0,
                'monthCount' => 0,
            ]);
        }

        // Ambil parameter filter dari frontend (opsional)
        $filter = $request->get('filter'); // nilai: hari_ini, kemarin, bulan_ini

        // Query awal: ambil transaksi milik kurir yang statusnya 'terkirim'
        $query = Transaksii::where('kurir_id', $kurir->kurir_id)
                    // ->with('pengguna.user') // load relasi pengguna dan user-nya
                    ->where('status', 'selesai');

        // Filter berdasarkan tanggal, jika ada filter dari request
        if ($filter === 'kemarin') {
            $query->whereDate('waktu_selesai', Carbon::yesterday());
        } elseif ($filter === 'hari_ini') {
            $query->whereDate('waktu_selesai', Carbon::today());
        } elseif ($filter === 'bulan_ini') {
            $query->whereMonth('waktu_selesai', Carbon::now()->month)
                ->whereYear('waktu_selesai', Carbon::now()->year);
        }

        // Ambil hasil transaksi setelah difilter dan diurutkan dari yang terbaru
        $transaksii = $query->orderBy('waktu_selesai', 'desc')
                        ->get([
                            // 'id',
                            'no_resi',
                            'nama_barang',
                            'alamat_tujuan',
                            // 'pengguna_id',
                            'pengirim',
                            'penerima',
                            'rating',
                            'status',
                            'waktu'
                        ]);

        // Hitung total transaksi yang dikirim hari ini oleh kurir
        $todayCount = Transaksii::where('kurir_id', $kurir->kurir_id)
                        ->where('status', 'selesai')
                        ->whereDate('waktu_selesai', Carbon::today())
                        ->count();

        // Hitung total transaksi yang dikirim kemarin
        $yesterdayCount = Transaksii::where('kurir_id', $kurir->kurir_id)
                        ->where('status', 'selesai')
                        ->whereDate('waktu_selesai', Carbon::yesterday())
                        ->count();

        // Hitung total transaksi yang dikirim bulan ini
        $monthCount = Transaksii::where('kurir_id', $kurir->kurir_id)
                        ->where('status', 'selesai')
                        ->whereMonth('waktu_selesai', Carbon::now()->month)
                        ->whereYear('waktu_selesai', Carbon::now()->year)
                        ->count();

        // Kembalikan data transaksi dan total count dalam response JSON
        return response()->json([
            'success' => true,
            'data' => $transaksii,
            'todayCount' => $todayCount,
            'yesterdayCount' => $yesterdayCount,
            'monthCount' => $monthCount
        ]);
    }


    // public function show($kurir_id)
    // {
    //     $kurir = Kurir::findOrFail($kurir_id);
    //     $avg = DB::table('transaksii')
    //             ->where('kurir_id', $kurir_id)
    //             ->avg('rating');

    //     return response()->json([
    //         'kurir'     => $kurir,
    //         'avg_rating' => round($avg ?: 0, 2),
    //     ]);
    // }

    /**
     * Store a newly created kurir
     */
    public function store(StoreKurirRequest $request)
    {
        // Validasi data dari request menggunakan Form Request khusus
        $validatedData = $request->validated();
    
        // Jika ada file foto yang diunggah
        if ($request->hasFile('photo')) {
            // Cek apakah sebelumnya ada foto lama (jika proses ini digunakan untuk update, baris ini bisa menyebabkan error karena $kurir belum dibuat)
            if ($kurir->user->photo) {
                // Hapus file foto lama dari storage
                Storage::disk('public')->delete($kurir->user->photo);
            }
    
            // Simpan foto baru ke folder 'photo' di storage/public
            if ($request->hasFile('photo')) {
                $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
            }
            
        }
    
        // Simpan data kurir ke database
        $kurir = Kurir::create($validatedData);
    
        // Muat relasi user agar bisa diakses tanpa query tambahan
        $kurir->load('user');
    
        // Kembalikan response JSON dengan data kurir yang baru dibuat
        return response()->json([
            'success' => true,
            'kurir' => [
                'kurir_id' => $kurir->kurir_id,
                'status' => $kurir->status,
                'user' => [
                    'name' => $kurir->user->name,
                    'email' => $kurir->user->email,
                    'phone' => $kurir->user->phone,
                    'photo' => $kurir->user->photo,
                ],
            ],
        ]);
    }
    

    /**
     * Show a specific kurir
     */
    public function show(Kurir $kurir)
    {
        $kurir->load('user');

        $jumlahTransaksi = Transaksi::where('kurir_id', $kurir->kurir_id)->count();
    
        return response()->json([
            // 'kurir'=> ['status' => $kurir->status],
            'user' => [
                    'name' => $kurir->user->name,
                    'email' => $kurir->user->email,
                    'phone' => $kurir->user->phone,
                    'photo' => $kurir->user->photo,
                    'status' => $kurir->status,
                    // 'rating' => $kurir->rating,
                ],
            ]);
    
    }

    /**
     * Update an existing kurir
     */
    public function update(UpdateKurirRequest $request, Kurir $kurir)
    {
        // Validasi semua data yang dikirim menggunakan Form Request (UpdateKurirRequest)
        $validatedData = $request->validated();
    
        // Pastikan kurir_id tetap diambil dari request untuk update eksplisit (meski biasanya tidak diubah)
        $validatedData['kurir_id'] = $request->input('kurir_id'); 
    
        // Jika password diisi, hash dan simpan
        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            // Jika tidak diisi, hapus dari data agar tidak ter-update
            unset($validatedData['password']);
        }
    
        // Tangani upload foto baru jika ada
        if ($request->hasFile('photo')) {
            // Hapus foto lama dari storage jika ada
            if ($kurir->user->photo) {
                Storage::disk('public')->delete($kurir->user->photo);
            }
    
            // Simpan foto baru ke folder 'photo' di storage/public
            $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        }
    
        // Update data user terkait (relasi ke user)
        $kurir->user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            // 'password' bisa dimasukkan jika ada fitur ubah password untuk user
            'photo' => $validatedData['photo'] ?? $kurir->user->photo,
        ]);
    
        // Update data kurir itu sendiri
        $kurir->update($validatedData);
    
        // Kembalikan response JSON berisi data kurir yang sudah diperbarui
        return response()->json([
            'success' => true,
            'kurir' => [
                'kurir_id' => $kurir->kurir_id,
                'status' => $kurir->status,
                'rating' => $kurir->rating,
            ]
        ]);
    }
    



    public function toggleStatus($kurir_id)
    {
        // Ambil data kurir berdasarkan ID, jika tidak ditemukan akan melempar 404
        $kurir = Kurir::findOrFail($kurir_id);
    
        // Cek apakah status saat ini adalah "aktif"
        if ($kurir->status === 'aktif') {
            $kurir->status = 'nonaktif'; // Ubah ke "nonaktif"
        } 
        // Jika status saat ini adalah "nonaktif"
        elseif ($kurir->status === 'nonaktif') {
            $kurir->status = 'aktif'; // Ubah ke "aktif"
        } 
        // Jika status "sedang menerima orderan", tidak diizinkan mengubah
        else {
            return response()->json([
                'message' => 'Tidak dapat mengubah status saat sedang menerima orderan'
            ], 400);
        }
    
        // Simpan perubahan ke database
        $kurir->save();
    
        // Kembalikan response JSON dengan status terbaru
        return response()->json([
            'status' => $kurir->status
        ]);
    }
    

    /**
     * Get all kurir
     */
    public function get()
    {
        return response()->json([
            'success' => true,
            'data' => Kurir::all(),
            // 'data' => Kurir::select('kurir_id', 'status', 'rating')->get()
        ]);
    }

    // public function updateRating($kurir_id)
    // {
    //     $kurir = Kurir::findOrFail($kurir_id);

    //     // Hitung rata-rata rating dari semua transaksi selesai yang punya rating (tidak null)
    //     $averageRating = Transaksii::where('kurir_id', $kurir_id)
    //         ->where('status', 'selesai')
    //         ->whereNotNull('rating')
    //         ->avg('rating');

    //     // Update rating di tabel kurir
    //     $kurir->rating = round($averageRating, 2); // dibulatkan ke 2 angka di belakang koma
    //     $kurir->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Rating kurir berhasil diperbarui',
    //         'rating' => $kurir->rating
    //     ]);
    // }

    public function updateRating($kurirId)
    {
        // Cari kurir, kalau tidak ditemukan akan otomatis throw 404
        $kurir = Kurir::findOrFail($kurirId);

        // Hitung rata-rata rating dari pengiriman yang memiliki rating (tidak null)
        $averageRating = Pengiriman::where('kurir_id', $kurirId)
            ->whereNotNull('rating')
            ->avg('rating');

        // Update rating kurir, dibulatkan ke 2 angka di belakang koma
        $kurir->rating = round($averageRating, 2);
        $kurir->save();

        return response()->json([
            'success' => true,
            'message' => 'Rating kurir berhasil diperbarui',
            'rating' => $kurir->rating
        ]);
    }


    // public function updateRating($kurirId)
    // {
    //     $avgRating = Pengiriman::where('kurir_id', $kurirId)
    //         ->whereNotNull('rating')
    //         ->avg('rating');

    //     $kurir = Kurir::find($kurirId);
    //     if ($kurir) {
    //         $kurir->rating = $avgRating;
    //         $kurir->save();
    //     }
    // }


    // public function destroy(Kurir $kurir)
    // {
    //     // Hapus foto dari storage jika user memiliki foto
    //     if ($kurir->user && $kurir->user->photo) {
    //         Storage::disk('public')->delete($kurir->user->photo);
    //     }

    //     // Hapus data user yang terkait
    //     if ($kurir->user) {
    //         $kurir->user->delete();
    //     }

    //     // Hapus data kurir
    //     $kurir->delete();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Data kurir berhasil dihapus'
    //     ]);
    // }
}


