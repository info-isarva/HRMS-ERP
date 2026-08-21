<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MeetingReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $meeting;
    public $user;

    /**
     * Create a new message instance.
     *
     * @param $meeting
     * @param $user
     */
    public function __construct($meeting, $user)
    {
        $this->meeting = $meeting;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Meeting Reminder')
                    ->view('emails.meeting_reminder')
                    ->with([
                        'meeting' => $this->meeting,
                        'user' => $this->user,
                    ]);
    }
}