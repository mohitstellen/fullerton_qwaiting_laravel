<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtp extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $teamId;

    public function __construct($otp, $teamId = null)
    {
        $this->otp = $otp;
        $this->teamId = $teamId;
    }

    public function build()
    {
        return $this->subject('Your One-Time Password (OTP)')
            ->view('emails.send-otp')
            ->with([
                'otp' => $this->otp,
                'teamId' => $this->teamId
            ]);
    }
}
