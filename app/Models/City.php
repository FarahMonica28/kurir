<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Relasi: City milik sebuah Province.
     */
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    /**
     * Relasi: City sebagai asal kota dari transaksi.
     */
    public function asalTransaksii()
    {
        return $this->hasMany(Transaksii::class, 'asal_kota_id');
    }

    /**
     * Relasi: City sebagai tujuan kota dari transaksi.
     */
    public function tujuanTransaksii()
    {
        return $this->hasMany(Transaksii::class, 'tujuan_kota_id');
    }
}
