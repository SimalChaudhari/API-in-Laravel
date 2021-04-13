<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendPin extends Mailable
{
    use Queueable, SerializesModels;
    public $verify_pin;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($verify_pin)
    {
        $this->verify_pin = $verify_pin;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Email Verification')->view('emails.verification');
    }
}
