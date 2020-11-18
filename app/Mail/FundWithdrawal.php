<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class FundWithdrawal extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $amount;
    private $user_name;
    private $status;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($amount, $user_name, $status)
    {
        $this->amount = $amount;
        $this->user_name = $user_name;
        $this->status = $status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.fund_withdrawal')
            ->subject("Withdrawal Notification")
            ->with([
                'amount'    => $this->amount,
                'user_name'    => $this->user_name,
                'status'    => $this->status,
            ]);
    }
}
