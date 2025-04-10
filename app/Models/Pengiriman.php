<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengiriman';
    protected $primaryKey = 'id'; // Sesuaikan dengan primary key tabel

    // protected $table = 'pengiriman';

    protected $fillable = [
        'kurir_id',
        'paket',
        'status',
        'tanggal_pengiriman',
        'tanggal_penerimaan',
        'penerima',
        'alamat',
        'biaya'
    ];
    public function kurir()
    {
        return $this->belongsTo(Kurir::class, 'kurir_id', 'kurir_id');
    }
    // public function kurir()
    // {
    //     return $this->belongsTo(Kurir::class);
    // }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
