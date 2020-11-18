<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class PasswordResetRequest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    private $name;
    private $code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $code)
    {
        $this->name = $name;
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.forgot_password')
            ->with([
                'name' => $this->name,
                'code' => $this->code
            ]);
    }
}