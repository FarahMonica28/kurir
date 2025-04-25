<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengguna extends Model
{
    use HasFactory;
    protected $table = 'pengguna';
    protected $primaryKey = 'pengguna_id'; // Sesuaikan dengan primary key tabel

    // Kurir::where('pengguna_id', 22)->count();

    protected $fillable = [
        'user_id',
        'alamat',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // Relasi dengan model Pengiriman

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id', 'pengguna_id');
    }

}
