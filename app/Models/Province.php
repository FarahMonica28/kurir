<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Relasi: Province memiliki banyak kota.
     */
    public function cities()
    {
        return $this->hasMany(City::class, 'province_id');
    }

    /**
     * Relasi: Province sebagai asal pengiriman dari transaksi.
     */
    public function asalTransaksii()
    {
        return $this->hasMany(Transaksii::class, 'asal_provinsi_id');
    }

    /**
     * Relasi: Province sebagai tujuan pengiriman dari transaksi.
     */
    public function tujuanTransaksii()
    {
        return $this->hasMany(Transaksii::class, 'tujuan_provinsi_id');
    }
}
