<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PatientAppointmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $url;
    public $logo;
    public $appointmentDetails;

    public function __construct($name, $url, $logo, $appointmentDetails = [])
    {
        $this->name = $name;
        $this->url = $url;
        $this->logo = $logo;
        $this->appointmentDetails = $appointmentDetails;
    }

    public function build()
    {
        return $this->subject('Appointment Confirmation')
            ->view('emails.patient_appointment_confirmation');
    }
}
