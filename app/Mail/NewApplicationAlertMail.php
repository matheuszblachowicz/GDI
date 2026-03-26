<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewApplicationAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $hostname;
    public $username;
    public $appName;
    public $aiAnalysis;

    /**
     * Create a new message instance.
     */
    public function __construct($hostname, $username, $appName, $aiAnalysis)
    {
        $this->hostname = $hostname;
        $this->username = $username;
        $this->appName = $appName;
        $this->aiAnalysis = $aiAnalysis;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("Alerta de Segurança: Nova Instalação - {$this->appName}")
                    ->view('emails.new_application_alert');
    }
}