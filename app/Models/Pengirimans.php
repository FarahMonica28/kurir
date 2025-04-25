<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengirimans extends Model
{
    
    protected $table = 'pengirimans';

    protected $fillable = [
        'no_resi',
        'penerima',
        'alamat_tujuan',
        'status',
        'waktu_ambil',
        'waktu_kirim',
        'kurir_id'
    ];

    public function kurir()
    {
        return $this->belongsTo(User::class, 'kurir_id');
    }
}

