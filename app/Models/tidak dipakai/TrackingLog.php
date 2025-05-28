<?php

// app/Models/TrackingLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'status', 'catatan', 'waktu',
    ];

    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class);
    }
}

