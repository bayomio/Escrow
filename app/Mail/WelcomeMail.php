<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $activation_token;
    private $name;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $activation_token)
    {
        $this->name = $name;
        $this->activation_token = $activation_token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.new_account')
            ->subject("Welcome to GameKrow")
            ->with([
                'name'    => $this->name,
                'activation_token'    => $this->activation_token
            ]);
    }
}
