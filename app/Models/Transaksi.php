<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'no_transaksi',
        'alamat_asal',
        'alamat_tujuan',
        'penerima',
        'pengirim',
        'nama_barang',
        'berat_barang',
        'biaya',
        'kurir_id', // kalau ini foreign key, ubah jadi 'kurir_id'
        'nama_barang',
        'no_hp_penerima',
        'waktu',
        'status',
        'penilaian',
        'komentar',
    ];

    // Relasi ke Kurir
    // public function kurir()
    // {
    //     // return $this->belongsTo(Kurir::class, 'kurir', 'nama'); // jika `kurir` adalah nama
    //     return $this->belongsTo(Kurir::class, 'kurir_id'); // jika kamu ganti jadi ID
    // }
    // Transaksi.php
    // public function kurir()
    // {
    //     return $this->belongsTo(Kurir::class, 'kurir_id')->with('user');
    // }

    // // App\Models\Transaksi.php
public function kurir()
{
    return $this->belongsTo(Kurir::class, 'kurir_id', 'kurir_id');
}

}
