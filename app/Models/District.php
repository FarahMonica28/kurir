<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'districts';

    protected $fillable = [
        'id',
        'name',
        'city_id',
    ];
    /**
     * Relasi: District milik sebuah City.
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Relasi: District sebagai asal kecamatan dari transaksi.
     */
    public function asalTransaksii()
    {
        return $this->hasMany(Transaksii::class, 'asal_kecamatan_id');
    }

    /**
     * Relasi: District sebagai tujuan kecamatan dari transaksi.
     */
    public function tujuanTransaksii()
    {
        return $this->hasMany(Transaksii::class, 'tujuan_kecamatan_id');
    }
}
