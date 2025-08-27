<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';
    protected $fillable = [
        'nama', 'stok', 'kategori_id', 'harga', 'photo',
    ];

    public function kurangiStok(int $jumlah = 1)
    {
        $this->stok -= $jumlah;
        if ($this->stok < 0) {
            $this->stok = 0; // biar nggak minus
        }
        $this->save();
    }

    public function tambahStok(int $jumlah = 1)
    {
        $this->stok += $jumlah;
        $this->save();
    }

}
