<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
    {
        use HasFactory;

        protected $fillable = [
            'origin_id',
            'destination_id',
            'weight',
            'courier',
            'service_code',
            'service_name',
            'cost',
            'etd',
            'note',
            'expired_at',
        ];

        protected $dates = ['expired_at'];
    }

