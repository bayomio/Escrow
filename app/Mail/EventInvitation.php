<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class EventInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $event_name;
    private $invitee;
    private $time;
    private $streaming_link;
    private $event_id;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($event_name, $invitee, $time, $streaming_link, $event_id)
    {
        $this->event_name = $event_name;
        $this->invitee = $invitee;
        $this->time = $time;
        $this->streaming_link = $streaming_link;
        $this->event_id = $event_id;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.game_event_notification')
            ->subject("GameKrow Invite")
            ->with([
                'event_name'    => $this->event_name,
                'invitee'    => $this->invitee,
                'time'    => $this->time,
                'streaming_link'    => $this->streaming_link,
                'event_id'    => $this->event_id
            ]);
    }
}
