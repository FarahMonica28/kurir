<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Str;


class Transaksii extends Model
{
    protected $table = 'transaksii'; // sesuaikan nama tabel
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'no_resi', 'penerima', 'pengirim', 'no_hp_penerima', 'status', 'alamat_asal', 'alamat_tujuan', 'nama_barang',
        'berat_barang', 'ekspedisi', 'layanan', 'biaya', 'waktu',
        'status', 'rating', 'komentar',
        'asal_provinsi_id', 'asal_kota_id', 'tujuan_provinsi_id', 'tujuan_kota_id', 'asal_kecamatan_id', 'tujuan_kecamatan_id',
        'pengguna_id', 'pernah_digudang', 'status_pembayaran', 'no_hp_pengirim',
    ];

    protected static function generateNoResi()
    {
        $prefix = 'ABC-' . now()->format('Ymd');
        $random = strtoupper(Str::random(6));

        return $prefix . '-' . $random;
    }
    
    public function asalProvinsi() {
        return $this->belongsTo(Province::class, 'asal_provinsi_id');
    }

    public function asalKota() {
        return $this->belongsTo(City::class, 'asal_kota_id');
    }

    public function tujuanProvinsi() {
        return $this->belongsTo(Province::class, 'tujuan_provinsi_id');
    }

    public function tujuanKota() {
        return $this->belongsTo(City::class, 'tujuan_kota_id');
    }
    public function asalKecamatan() {
        return $this->belongsTo(District::class, 'asal_kecamatan_id');
    }
    public function tujuanKecamatan() {
        return $this->belongsTo(District::class, 'tujuan_kecamatan_id');
    }

    // public function pengguna() {
    //     return $this->belongsTo(User::class, 'pengguna_id');
    // }

    public function kurir() {
        return $this->belongsTo(Kurir::class, 'kurir_id');
    }
    public function pengguna() {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }
    public function pengiriman()
    {
        return $this->hasMany(Pengiriman::class, 'transaksii_id', 'id');
    }
    

}


// class Transaksii extends Model
// {
//     public function provinceOrigin()
//     {
//         return $this->belongsTo(Province::class, 'province_origin_id');
//     }

//     public function cityOrigin()
//     {
//         return $this->belongsTo(City::class, 'city_origin_id');
//     }

//     public function provinceDestination()
//     {
//         return $this->belongsTo(Province::class, 'province_destination_id');
//     }

//     public function cityDestination()
//     {
//         return $this->belongsTo(City::class, 'city_destination_id');
//     }
// }