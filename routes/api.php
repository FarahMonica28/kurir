<?php

// use App\Http\Controllers\TrackingController;
// use App\Http\Controllers\tidakdipakai\PengirimanController;
// use App\Http\Controllers\Api\PengirimansController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PengirimanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckOngkirController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\TransaksiiController;
use App\Http\Controllers\TransController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\XenditController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\KurirController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\RajaOngkirController;

use App\Http\Controllers\BinderbyteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShippingController;
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

Route::post('/auth/register/get/email/otp', [AuthController::class, 'sendEmailOtp']);
Route::post('/auth/register/check/email/otp', [AuthController::class, 'checkEmailOtp']);

// Authentication Route
Route::middleware(['auth', 'json'])->prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('auth');
    Route::delete('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::prefix('setting')->group(function () {
    Route::get('', [SettingController::class, 'index']);
});
Route::get('/rajaongkir/provinces', [RajaOngkirController::class, 'getProvinces']);
Route::get('/rajaongkir/cities', [RajaOngkirController::class, 'getCities']);
Route::get('/province', [CheckOngkirController::class, 'getProvinces']);
Route::get('/cities/{province_id}', [CheckOngkirController::class, 'getCities']);
Route::post('/ongkir', [CheckOngkirController::class, 'checkOngkir']);
    // Route::post('/track-waybill', [CheckOngkirController::class, 'trackWaybill']);

    
    
Route::post('/transaksii', [TransaksiiController::class, 'index']);
Route::get('/transaksii/{id}', [TransaksiiController::class, 'get']);
Route::post('/transaksii/store', [TransaksiiController::class, 'store']);
Route::get('/provinces', [TransaksiiController::class, 'getProvinces']);
Route::get('/cities/{provinceId}', [TransaksiiController::class, 'getCities']);
Route::post('/cost', [TransaksiiController::class, 'hitungOngkir']);
    // routes/api.php
Route::put('/transaksii/{id}/antar', [TransaksiiController::class, 'antar']);
Route::put('/transaksii/{id}/ambil', action: [TransaksiiController::class, 'ambil']);
Route::put('/transaksii/{id}/gudang', [TransaksiiController::class, 'gudang']);
Route::post('/rating', action: [TransaksiiController::class, 'storePenilaian']);

// Route::post('/xendit/callback', [TransaksiiController::class, 'handleCallback']);

// Route::post('/payment', [TransaksiiController::class, 'payment']);
// Route::post('/payment', [PembayaranController::class, 'createInvoice']);
// Route::post('/payment', [PembayaranController::class, 'store']);
// Route::prefix('v1')->group(function () {
    // Route::post('/payment', [PembayaranController::class, 'payment']);
    //     Route::post('/payment', [PembayaranController::class, 'store']);
Route::get('/payment/success', function () {
    return view('payment.success');
})->withoutMiddleware(['auth', 'permission']); // atau 'web' saja
// Route::post('/midtrans/notification', [MidtransController::class, 'handleNotification']);

// Route::post('/midtrans/notification', [PaymentController::class, 'handleNotification']);
Route::get('/payment/{id}', [PaymentController::class, 'show'])->name('payment.show');
// Route::post('/pay', [PaymentController::class, 'pay']);
Route::get('/payment/token/{id}', [PaymentController::class, 'getSnapToken']);
// Route::post('/webhook/midtrans', [PaymentController::class, 'handleNotification']);
// Route::post('/webhook/midtrans', [PaymentController::class, 'handleWebhook']);

Route::post('/midtrans/notification', [PaymentController::class, 'handleNotification']);
Route::post('/manual-update-status', [Paymentcontroller::class, 'manualUpdateStatus']);

// Route::post('/payment/callback', [paymentController::class, 'callback']);

Route::post('/verify-email', [AuthController::class, 'verifyEmailOtp']);
// Route::post('/verify-email', [AuthController::class, 'verifyEmailOtp']);
 


Route::get('/tracking/{no_resi}', [TrackingController::class, 'tracking']);
// routes/api.php
// Route::get('/tracking/{no_resi}', [TransaksiController::class, 'tracking']);


Route::get('/cek-ongkir', [BinderbyteController::class, 'cekOngkir']);
Route::get('/cek-resi', [BinderbyteController::class, 'cekResi']);
Route::get('/ongkir', [ShippingController::class, 'checkOngkir']);

Route::get('/cek-ongkir', function (Request $request, ShippingController $controller) {
    return $controller->getShippingCost(
        $request->input('origin'),
        $request->input('destination'),
        $request->input('weight'),
        $request->input('courier')
    );
});


Route::post('/ongkirr', [BinderbyteController::class, 'cekOngkir']);

Route::post('/otp/send/{uuid}', [OtpController::class, 'send']);
// Verifikasi OTP
Route::post('/otp/verify/{uuid}', [OtpController::class, 'verify']);

// Route::get('/checkout', 'PaymentController@checkout');

//semua masuk ke sini
Route::middleware(['auth', 'json'])->group(function () {
    Route::prefix('setting')->middleware('can:setting')->group(function () {
        Route::post('', [SettingController::class, 'update']);
    });
    // Route::post('/payment', [TransaksiiController::class, 'payment']);
    Route::prefix('master')->group(function () {
        Route::middleware('can:master-user')->group(function () {
            Route::get('users', [UserController::class, 'get']);
            Route::get('/users/{user}', [UserController::class, 'show']);
            Route::post('/users', [UserController::class, 'index']);
            Route::get('master/users', [UserController::class, 'index']);
            Route::post('users/store', [UserController::class, 'store']);
            // routes/api.php
            Route::get('/me', [UserController::class, 'me']);
            Route::get('/admin/dashboard-summary', [UserController::class, 'dashboardSummary']);
            Route::post('/master/users/request-otp', [UserController::class, 'requestOtp']);
            Route::post('/master/users/verify-otp', [UserController::class, 'verifyOtp']);
            // Kirim OTP
            
            // Route::post('/otp/send', [OtpController::class, 'send']);
            Route::post('/otp/send/{uuid}', [OtpController::class, 'send']);
            // Verifikasi OTP
            // Route::post('/otp/verify', [OtpController::class, 'verify']);
            Route::post('/otp/verify/{uuid}', [OtpController::class, 'verify']);
            
            
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
        // Route::get('kurir', [KurirController::class, 'get'])->withoutMiddleware('can:kurir');
        Route::get('/get-kurir', [KurirController::class, 'get'])->withoutMiddleware('can:kurir');
        Route::post('kurir/store', [KurirController::class, 'store']);
        Route::post('kurir', [KurirController::class, 'index']);
        Route::put('/kurir-update', [KurirController::class, 'update']);
        // routes/api.php
        // Route::get('/kurir', [KurirController::class, 'profile'])->middleware('auth:sanctum');
        // Route::get('/kurir/list', [KurirController::class, 'list']);
        Route::middleware('auth:sanctum')->get('/kurir/ringkasan', [KurirController::class, 'ringkasanKurir']);
        Route::put('/kurir/{kurir_id}/toggle-status', [KurirController::class, 'toggleStatus']);
        Route::put('/kurir/{kurir_id}', [KurirController::class, 'updateKurir']);
        Route::put('/transaksi/{transaksi_id}/status', [KurirController::class, 'updateStatusTransaksi']);
        Route::put('/kurir/{id}/update-rating', [KurirController::class, 'updateRating']);
        Route::get('/kurir/transaksii-count', [KurirController::class, 'transaksiiCount']);
        Route::middleware('can:kurir')->get('/kurir/transaksii-list', [KurirController::class, 'transaksiiList']);
        Route::get('/rating/{id}', [KurirController::class, 'show']);
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
    Route::prefix('kurir')->group(function () {
        Route::get('/', [KurirController::class, 'index']); // List kurir dengan pagination
        Route::post('/store', [KurirController::class, 'store']); // Tambah kurir

        Route::get('/{kurir}', [KurirController::class, 'show']); // Lihat detail kurir
        Route::put('/{kurir}', [KurirController::class, 'update']); // Update kurir
        Route::delete('/{kurir}', [KurirController::class, 'destroy']); // Hapus kurir
    });

    Route::middleware('can:pengiriman')->group(function () {
        Route::get('pengiriman', [PengirimanController::class, 'get'])->withoutMiddleware('can:pengiriman');
        Route::get('pengiriman/create', [PengirimanController::class, 'create'])->name('pengiriman.create');
        Route::post('pengiriman/store', [PengirimanController::class, 'store']);
        Route::post('pengiriman', [PengirimanController::class, 'index'])->withoutMiddleware('can:pengiriman');
        Route::apiResource('pengiriman', PengirimanController::class)
        ->except(['index', 'store']);
    });

    Route::middleware(['auth:sanctum', 'role:kurir'])->group(function () {
        Route::put('/pengiriman/{id}/status', [PengirimanController::class, 'updateStatus']);
        Route::get('/kurir/pengiriman', [PengirimanController::class, 'pengirimanKurir']);
    });

    //tracking
    Route::middleware('can:tracking')->group(function () {
        // HANYA satu GET untuk tracking dengan query resi
        Route::get('/tracking', [TrackingController::class, 'track']);   
        // Jika kamu juga butuh lihat detail berdasarkan resi via path parameter
           
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


    // Route::prefix('pengirimans')->group(function () {
    //     Route::get('/pengirimans', [PengirimansController::class, 'index']);                // GET semua data (dengan search & paginate)
    //     Route::post('/pengirimans', [PengirimansController::class, 'store']);               // POST tambah data
    //     // Route::get('/pengirimans{id}', [PengirimansController::class, 'get']);              // GET detail data by ID
    //     Route::get('pengirimans{id}', [PengirimansController::class, 'get'])->withoutMiddleware('can:pengirimans');
    //     Route::put('/pengirimans{id}', [PengirimansController::class, 'update']);           // PUT update data by ID
    //     Route::delete('/pengirimans{id}', [PengirimansController::class, 'destroy']);       // DELETE data by ID
    //     Route::put('/pengirimans{id}/status', [PengirimansController::class, 'updateStatus']); // PUT update status pengiriman

    //     Route::post('/pengirimans/{id}/mulai', [PengirimansController::class, 'mulai']);
    //     Route::post('/pengirimans/{id}/selesai', [PengirimansController::class, 'selesai']);

    //     // Operasi khusus tracking
    //     Route::post('/ambil-barang', [PengirimansController::class, 'ambilBarang']);
    //     Route::post('/mulai-kirim', [PengirimansController::class, 'mulaiKirim']);
    //     Route::post('/selesai-kirim', [PengirimansController::class, 'selesaiKirim']);
    //     Route::post('/lapor-masalah', [PengirimansController::class, 'laporkanMasalah']);
    // });
    
    //transaksi
    Route::middleware('can:orderan')->group(function () {
        // Menampilkan list transaksi (pada halaman index)
        Route::get('transaksi', [TransaksiController::class, 'get'])->withoutMiddleware('can:orderan');
        Route::post('orderan', [TransaksiController::class, 'index'])->withoutMiddleware('can:orderan'); // ini mengijinkan kurir mengambil data yang dimasukan pengguna

    });
    Route::middleware('can:transaksi')->group(function () {
        // Menampilkan list transaksi (pada halaman index)
        Route::get('transaksi', [TransaksiController::class, 'get'])->withoutMiddleware('can:transaksi');
        // Route::post('transaksi', [TransaksiController::class, 'index'])->withoutMiddleware('can:transaksi'); // ini mengijinkan kurir mengambil data yang dimasukan pengguna
        Route::post('transaksi', [TransaksiController::class, 'index'])->withoutMiddleware('can:transaksi'); // => ini salah mangkanya role kurir tidak memunculkan data        Route::get('pengirimans{id}', [PengirimansController::class, 'get'])->withoutMiddleware('can:pengirimans');
        // Halaman untuk membuat transaksi baru
        Route::get('transaksi/create', [TransaksiController::class, 'create'])->name('transaksi.create');
        // Route::get('/transaksi', [TransaksiController::class, 'index'])->middleware('can:viewAny,App\Models\Transaksi');
        // Menyimpan transaksi baru
        Route::post('transaksi/store', [TransaksiController::class, 'store']);
        // Menampilkan list transaksi (misalnya untuk paginasi)
        Route::put('/transaksi/update-status/{id}', [TransaksiController::class, 'updateStatus']);
        Route::post('/transaksi/storer', action: [TransaksiController::class, 'storePenilaian']);
        // Route::middleware('auth')->group(function () {
        //     // Route::put('/transaksi/{transaksi}', [TransaksiController::class, 'update']);
        // });
        Route::apiResource('transaksi', TransaksiController::class)
            ->except(['index', 'store']);
        // CRUD transaksi kecuali index dan store
    });

    //trans    
    Route::middleware('can:trans')->group(function () {
        // Menampilkan list transaksi (pada halaman index)
        Route::get('trans', [TransController::class, 'get'])->withoutMiddleware('can:trans');
        Route::post( 'trans', [TransaksiController::class, 'index'])->withoutMiddleware('can:trans'); // ini mengijinkan kurir mengambil data yang dimasukan pengguna
        // Route::post('trans', [TransController::class, 'index']); // ini salah mangkanya role kurir tidak memunculkan data        Route::get('pengirimans{id}', [PengirimansController::class, 'get'])->withoutMiddleware('can:pengirimans');
        // Halaman untuk membuat trans baru
        Route::get('trans/create', [TransController::class, 'create'])->name('trans.create');
        Route::post('trans/store', [TransController::class, 'store']);
        // Menampilkan list trans (misalnya untuk paginasi)
        // Route::get('/trans', [TransController::class, 'show']); 
        Route::get('/trans/{transaksi}', [TransaksiController::class, 'show']);
        Route::put('/trans/update-status/{id}', [TransController::class, 'updateStatus']);        // Route::post('/trans/storer', [TransController::class, 'storePenilaian']);
        Route::put('/transaksi/{id}/update-status', [TransController::class, 'updateStatus'])->withoutMiddleware('can:trans');
        // Route::middleware('auth')->group(function () {
            //     // Route::put('/trans/{trans}', [TransController::class, 'update']);
            // });
            Route::apiResource('trans', TransController::class)
            ->except(['index', 'store']);
            // Route::middleware(['auth', 'can:update,trans'])->put('/trans/{trans}', [TransaksiController::class, 'update']);
        // CRUD trans kecuali index dan store
    });

   
    // routes/api.php
    // Route::get('/rajaongkir/cities', [RajaOngkirController::class, 'getCities']);
    
    // Route::get('/ongkir', [CheckOngkirController::class, 'index']);
    // Route::post('/ongkir', [CheckOngkirController::class, 'check_ongkir']);
    // Route::get('/cities/{province_id}', [CheckOngkirController::class, 'getCities']);
    // routes/api.php
    // Route::post('/cost', [RajaOngkirController::class, 'getCost']);
    // Route::post('/cost', [RajaOngkirController::class, 'cost']);
    
    Route::post( '/gudang', [GudangController::class, 'index'])->withoutMiddleware('can:gudang'); // ini mengijinkan kurir mengambil data yang dimasukan pengguna




    // Route::get('/ongkir', 'CheckOngkirController@index');
    // Route::post('/ongkir', 'CheckOngkirController@check_ongkir');
    // Route::get('/cities/{province_id}', 'CheckOngkirController@getCities');


    
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