<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class GameReminderVoters extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $start;
    private $name;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $start)
    {
        $this->name = $name;
        $this->start = $start;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.gaming_reminder_voters')
            ->subject("Reminder Notification")
            ->with([
                'name'    => $this->name,
                'start'    => $this->start,
            ]);
    }
}
