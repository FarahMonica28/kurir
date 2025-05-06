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
        // 'pengirim',
        'nama_barang',
        'jarak',
        'biaya',
        'kurir_id', // kalau ini foreign key, ubah jadi 'kurir_id'
        'pengguna_id', // kalau ini foreign key, ubah jadi 'kurir_id'
        'no_hp_penerima',
        'waktu',
        'status',
        'penilaian',
        'komentar',
    ];

    // Relasi ke Kurir
    public function kurir()
    {
        return $this->belongsTo(Kurir::class, 'kurir_id', 'kurir_id');
    }
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id', 'pengguna_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'pengguna_id', 'id');
    }



}
