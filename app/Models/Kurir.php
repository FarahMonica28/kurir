<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurir extends Model
{
    use HasFactory;
    protected $table = 'kurir';
    protected $primaryKey = 'kurir_id'; // Sesuaikan dengan primary key tabel

    // Kurir::where('kurir_id', 22)->count();

    protected $fillable = [
        'user_id',
        'rating',
        'status',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed', // untuk menyimpan didatabase nya secara acak
    ];

    // Relasi dengan model Pengiriman
    public function pengiriman()
    {
        // return $this->hasMany(Pengiriman::class, 'kurir_id');
        return $this->hasMany(Pengiriman::class, 'kurir_id');
    }    

    public $timestamps = false; // Jika tidak ada created_at & updated_at

    protected $appends = ['avg_rating'];

    public function getAvgRatingAttribute()
    {
        // Menghitung rata-rata langsung dari database
        return $this->transaksii()->avg('rating') ?? 0;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'kurir_id', 'kurir_id');
    }
    public function transaksii()
    {
        return $this->hasMany(Transaksii::class, 'kurir_id', 'kurir_id');
    }

}
