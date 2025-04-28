<?php

// use App\Http\Controllers\TrackingController;
use App\Http\Controllers\Api\PengirimansController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\TransController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KurirController;
use App\Http\Controllers\PengirimanController;
use App\Http\Controllers\TrackingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Route
Route::middleware(['auth', 'json'])->prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('auth');
    Route::delete('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::prefix('setting')->group(function () {
    Route::get('', [SettingController::class, 'index']);
});

//semua masuk ke sini
Route::middleware(['auth', 'verified', 'json'])->group(function () {
    Route::prefix('setting')->middleware('can:setting')->group(function () {
        Route::post('', [SettingController::class, 'update']);
    });

    Route::prefix('master')->group(function () {
        Route::middleware('can:master-user')->group(function () {
            Route::get('users', [UserController::class, 'get']);
            Route::get('/users/{user}', [UserController::class, 'show']);
            Route::post('users', [UserController::class, 'index']);
            Route::post('users/store', [UserController::class, 'store']);
            // routes/api.php
            Route::get('/me', [UserController::class, 'me']);
            Route::apiResource('users', UserController::class)
                ->except(['index', 'store'])->scoped(['user' => 'uuid']);

        });

        Route::middleware('can:master-role')->group(function () {
            Route::get('roles', [RoleController::class, 'get'])->withoutMiddleware('can:master-role');
            Route::post('roles', [RoleController::class, 'index']);
            Route::post('roles/store', [RoleController::class, 'store']);
            Route::apiResource('roles', RoleController::class)
            ->except(['index', 'store']);
        });
    });

    //kurir
    Route::middleware('can:kurir')->group(function () {
        Route::get('kurir', [KurirController::class, 'get'])->withoutMiddleware('can:kurir');
        Route::post('kurir/store', [KurirController::class, 'store']);
        Route::post('kurir', [KurirController::class, 'index']);
        // routes/api.php
        // Route::get('/kurir', [KurirController::class, 'profile'])->middleware('auth:sanctum');
        // Route::get('/kurir/get', [KurirController::class, 'get']);
        Route::get('/kurir/list', [KurirController::class, 'list']);
        Route::put('/kurir/{kurir_id}/toggle-status', [KurirController::class, 'toggleStatus']);
        Route::apiResource('kurir', KurirController::class)
        ->except(['index', 'store']);
    });

    //pengguna
    Route::middleware('can:pengguna')->group(function () {
        Route::get('pengguna', [PenggunaController::class, 'get'])->withoutMiddleware('can:pengguna');
        Route::post('pengguna/store', [PenggunaController::class, 'store']);
        Route::post('pengguna', [PenggunaController::class, 'index']);
        Route::apiResource('pengguna', PenggunaController::class)
        ->except(['index', 'store']);
    });

    //pengiriman 
    // Route::prefix('kurir')->group(function () {
    //     Route::get('/', [KurirController::class, 'index']); // List kurir dengan pagination
    //     Route::post('/store', [KurirController::class, 'store']); // Tambah kurir

        // Route::get('/{kurir}', [KurirController::class, 'show']); // Lihat detail kurir
        // Route::put('/{kurir}', [KurirController::class, 'update']); // Update kurir
        // Route::delete('/{kurir}', [KurirController::class, 'destroy']); // Hapus kurir
    // });

    Route::middleware('can:pengiriman')->group(function () {
        Route::get('pengiriman', [PengirimanController::class, 'get'])->withoutMiddleware('can:pengiriman');
        Route::get('pengiriman/create', [PengirimanController::class, 'create'])->name('pengiriman.create');
        Route::post('pengiriman/store', [PengirimanController::class, 'store']);
        Route::post('pengiriman', [PengirimanController::class, 'index']);
        Route::apiResource('pengiriman', PengirimanController::class)
        ->except(['index', 'store']);
    });

    // Route::middleware(['auth:sanctum', 'role:kurir'])->group(function () {
    //     Route::put('/pengiriman/{id}/status', [PengirimanController::class, 'updateStatus']);
    //     Route::get('/kurir/pengiriman', [PengirimanController::class, 'pengirimanKurir']);
    // });

    //tracking
    Route::middleware('can:tracking')->group(function () {
        // HANYA satu GET untuk tracking dengan query resi
        Route::get('/tracking', [TrackingController::class, 'track']);   
        // Jika kamu juga butuh lihat detail berdasarkan resi via path parameter
        Route::get('/tracking/{no_resi}', [TrackingController::class, 'show']);   
        // Tambah data
        Route::post('/tracking/store', [TrackingController::class, 'store']);   
        // Resource, kecuali yang sudah di-override
        Route::apiResource('tracking', TrackingController::class)->except(['index', 'store','show']);
    });

    //pengirimans pengguna
    // Route::prefix('pengirimans')->group(function () {
    //     Route::post('/ambil-barang', [PengirimansController::class, 'ambilBarang']);
    //     Route::post('/mulai-kirim', [PengirimansController::class, 'mulaiKirim']);
    //     Route::post('/selesai-kirim', [PengirimansController::class, 'selesaiKirim']);
    //     Route::post('/pengirimans/laporkan-masalah', [PengirimansController::class, 'laporkanMasalah']);
    // });
    Route::prefix('pengirimans')->group(function () {
        Route::get('/pengirimans', [PengirimansController::class, 'index']);                // GET semua data (dengan search & paginate)
        Route::post('/pengirimans', [PengirimansController::class, 'store']);               // POST tambah data
        // Route::get('/pengirimans{id}', [PengirimansController::class, 'get']);              // GET detail data by ID
        Route::get('pengirimans{id}', [PengirimansController::class, 'get'])->withoutMiddleware('can:pengirimans');
        Route::put('/pengirimans{id}', [PengirimansController::class, 'update']);           // PUT update data by ID
        Route::delete('/pengirimans{id}', [PengirimansController::class, 'destroy']);       // DELETE data by ID
        Route::put('/pengirimans{id}/status', [PengirimansController::class, 'updateStatus']); // PUT update status pengiriman

        Route::post('/pengirimans/{id}/mulai', [PengirimansController::class, 'mulai']);
        Route::post('/pengirimans/{id}/selesai', [PengirimansController::class, 'selesai']);

        // Operasi khusus tracking
        Route::post('/ambil-barang', [PengirimansController::class, 'ambilBarang']);
        Route::post('/mulai-kirim', [PengirimansController::class, 'mulaiKirim']);
        Route::post('/selesai-kirim', [PengirimansController::class, 'selesaiKirim']);
        Route::post('/lapor-masalah', [PengirimansController::class, 'laporkanMasalah']);
    });
    
    //transaksi
    Route::middleware('can:transaksi')->group(function () {
        // Menampilkan list transaksi (pada halaman index)
        Route::get('transaksi', [TransaksiController::class, 'get'])->withoutMiddleware('can:transaksi');
        // Route::post('transaksi', [TransaksiController::class, 'index'])->withoutMiddleware('can:transaksi'); // ini mengijinkan kurir mengambil data yang dimasukan pengguna
        Route::post('transaksi', [TransaksiController::class, 'index']); // => ini salah mangkanya role kurir tidak memunculkan data        Route::get('pengirimans{id}', [PengirimansController::class, 'get'])->withoutMiddleware('can:pengirimans');
        // Halaman untuk membuat transaksi baru
        Route::get('transaksi/create', [TransaksiController::class, 'create'])->name('transaksi.create');
        // Route::get('/transaksi', [TransaksiController::class, 'index'])->middleware('can:viewAny,App\Models\Transaksi');
        // Menyimpan transaksi baru
        Route::post('transaksi/store', [TransaksiController::class, 'store']);
        // Menampilkan list transaksi (misalnya untuk paginasi)
        Route::put('/transaksi/update-status/{id}', [TransaksiController::class, 'updateStatus']);
        Route::post('/transaksi/storer', [TransaksiController::class, 'storePenilaian']);
        // Route::middleware('auth')->group(function () {
        //     // Route::put('/transaksi/{transaksi}', [TransaksiController::class, 'update']);
        // });
        Route::apiResource('transaksi', TransaksiController::class)
            ->except(['index', 'store']);
        // Route::middleware(['auth', 'can:update,transaksi'])->put('/transaksi/{transaksi}', [TransaksiController::class, 'update']);
        // CRUD transaksi kecuali index dan store
    });

    //trans    
    Route::middleware('can:trans')->group(function () {
        // Menampilkan list transaksi (pada halaman index)
        Route::get('trans', [TransController::class, 'get'])->withoutMiddleware('can:trans');
        Route::post('trans', [TransController::class, 'index'])->withoutMiddleware('can:trans'); // ini mengijinkan kurir mengambil data yang dimasukan pengguna
        // Route::post('trans', [TransController::class, 'index']); // ini salah mangkanya role kurir tidak memunculkan data        Route::get('pengirimans{id}', [PengirimansController::class, 'get'])->withoutMiddleware('can:pengirimans');
        // Halaman untuk membuat trans baru
        Route::get('trans/create', [TransController::class, 'create'])->name('trans.create');
        Route::post('trans/store', [TransController::class, 'store']);
        // Menampilkan list trans (misalnya untuk paginasi)
        // Route::get('/trans', [TransController::class, 'show']); 
        Route::get('/trans/{transaksi}', [TransaksiController::class, 'show']);
        Route::put('/trans/update-status/{id}', [TransController::class, 'updateStatus']);
        // Route::post('/trans/storer', [TransController::class, 'storePenilaian']);
        // Route::middleware('auth')->group(function () {
        //     // Route::put('/trans/{trans}', [TransController::class, 'update']);
        // });
        Route::apiResource('trans', TransController::class)
            ->except(['index', 'store']);
        // Route::middleware(['auth', 'can:update,trans'])->put('/trans/{trans}', [TransaksiController::class, 'update']);
        // CRUD trans kecuali index dan store
    });
    
});

// Route::get('/tracking/{no_resi}', [TrackingController::class, 'track']);
// // routes/api.php        Route::get('pengirimans{id}', [PengirimansController::class, 'get'])->withoutMiddleware('can:pengirimans');
// Route::get('/tracking/show', [TrackingController::class, 'show']);

// Route::get('/tracking/{no_resi}', [TrackingController::class, 'show']);




// Route::prefix('pengiriman')->group(function () {
//     Route::get('/', [PengirimanController::class, 'index']); // List semua pengiriman
//     Route::post('/', [PengirimanController::class, 'store']); // Tambah pengiriman baru
//     Route::get('/{pengiriman}', [PengirimanController::class, 'show']); // Detail pengiriman
//     Route::put('/{pengiriman}', [PengirimanController::class, 'update']); // Update pengiriman
//     Route::delete('/{pengiriman}', [PengirimanController::class, 'destroy']); // Hapus pengiriman
// });