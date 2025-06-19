<?php

// app/Models/Pengiriman.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';
    
    protected $fillable = [
        'id',
        'transaksii_id', 
        'checkout_link',
        'external_id',
        'status',
    ];
}