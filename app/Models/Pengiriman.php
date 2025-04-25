<?php

// app/Models/Pengiriman.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengiriman';
    
    protected $fillable = [
        'no_resi', 'paket', 'penerima', 'alamat',
        'kurir_id', 'status', 'tanggal_dibuat','tanggal_pengiriman',
        'tanggal_penerimaan', 'biaya',
    ];

    public function trackingLogs()
    {
        return $this->hasMany(TrackingLog::class);
    }

    public function kurir()
    {
        return $this->belongsTo(Kurir::class);
    }
    
}
