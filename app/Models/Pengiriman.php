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
        'deskripsi',
        'kurir_id', 
        'status',
        'transaksii_id',
        'rating',
        'komentar',
    ];

    public function trackingLogs()
    {
        return $this->hasMany(TrackingLog::class);
    }

    public function kurir()
    {
        return $this->belongsTo(Kurir::class,'kurir_id');
    }
        public function transaksii()
    {
        return $this->belongsTo(Transaksii::class, 'transaksii_id', 'id');
    }

}
