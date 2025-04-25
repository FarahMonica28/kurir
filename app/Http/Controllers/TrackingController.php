<?PHP
// app/Http/Controllers/Api/TrackingController.php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class TrackingController extends Controller
{
    public function show($no_resi)
    {
        // Mengambil pengiriman berdasarkan no_resi, dengan relasi kurir dan user
        $pengiriman = Pengiriman::join('kurir','kurir.kurir_id', '=', 'pengiriman.kurir_id')->join('users','users.id','=','kurir.user_id')
            ->where('no_resi', $no_resi)
            ->first();

        // Jika pengiriman tidak ditemukan, beri response 404

        Log::info('cek');
        Log::info($no_resi);
        Log::info($pengiriman);
        if (!$pengiriman) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // Menyiapkan status berdasarkan tanggal yang tersedia
        $status = [
            [
                'label' => 'Pesanan Dibuat',
                'timestamp' =>  $pengiriman->tanggal_dibuat ? $pengiriman->tanggal_dibuat->toDateTimeString() : null,
                'completed' => (bool) $pengiriman->tanggal_dibuat,
            ],
            [
                'label' => 'Dikirim',
                'timestamp' => $pengiriman->tanggal_pengiriman ? $pengiriman->tanggal_pengiriman : null,
                'completed' => (bool) $pengiriman->tanggal_pengiriman,
            ],
            [
                'label' => 'Diterima',
                'timestamp' => $pengiriman->tanggal_penerimaan ? $pengiriman->tanggal_penerimaan : null,
                'completed' => (bool) $pengiriman->tanggal_penerimaan,
            ]
        ];

        // Mengembalikan response JSON dengan data pengiriman dan status
        return response()->json([
            'no_resi' => $pengiriman->no_resi,
            'penerima' => $pengiriman->penerima,
            'alamat' => $pengiriman->alamat,
            'kurir' => $pengiriman->name?? '-',
            'status' => $status,
        ]);
    }

    public function track(Request $request)
    {   
        // Mengambil parameter 'resi' dari query string
        $no_resi = $request->query('resi');
    
        // Cek apakah parameter 'resi' ada
        if (!$no_resi) {
            return response()->json(['message' => 'Resi diperlukan'], 400);
        }
    
        // Mencari pengiriman berdasarkan no_resi
        $pengiriman = Pengiriman::where('no_resi', $no_resi)->first();
    
        // Jika pengiriman tidak ditemukan, beri response 404
        if (!$pengiriman) {
            return response()->json(['message' => 'Pengiriman tidak ditemukan'], 404);
        }
    
        // Mengembalikan response JSON dengan detail pengiriman
        return response()->json($pengiriman);
    }
}
