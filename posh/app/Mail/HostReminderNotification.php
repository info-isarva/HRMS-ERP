<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HostReminderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $meeting;

    /**
     * Create a new message instance.
     *
     * @param $meeting
     */
    public function __construct($meeting)
    {
        $this->meeting = $meeting;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Meeting Reminder')
                    ->view('emails.host_reminder_notification')
                    ->with(['meeting' => $this->meeting]);
    }
}