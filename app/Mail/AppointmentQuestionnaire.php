<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentQuestionnaire extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $url;
    public $logo;

    public function __construct($name, $url, $logo)
    {
        $this->name = $name;
        $this->url = $url;
        $this->logo = $logo;
    }

    public function build()
    {
        return $this->subject('Appointment Questionnaire')
            ->view('emails.appointment_questionnaire');
    }
}
