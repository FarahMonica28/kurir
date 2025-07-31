<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    // public $nama;

    public function __construct($otp)
    {
        $this->otp = $otp;
        // $this->nama = $nama;
    }

    public function build()
    {
        return $this->subject('Kode OTP Anda')
                    ->view('emails.otp')
                    ->with(['otp' => $this->otp]);
    }
}
