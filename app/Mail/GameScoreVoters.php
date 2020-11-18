<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class GameScoreVoters extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $event_name;
    private $user_name;
    private $winner;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user_name, $event_name, $winner)
    {
        $this->user_name = $user_name;
        $this->event_name = $event_name;
        $this->winner = $winner;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.gaming_score_voters')
            ->subject("Reminder Notification")
            ->with([
                'user_name'    => $this->user_name,
                'event_name'    => $this->event_name,
                'winner'    => $this->winner,
            ]);
    }
}
