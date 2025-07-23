<?php

// app/Models/EmailVerification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class EmailVerification extends Model
{

        protected $table = 'email_verifications';

    protected $fillable = ['user_id', 'otp', 'expires_at'];

    public $timestamps = true;

    public function isExpired()
    {
        return Carbon::now()->greaterThan($this->expires_at);
    }
}
