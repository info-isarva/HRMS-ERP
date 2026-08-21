<?php

namespace App\Mail;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $task;
    public $user;
    public $relatedItem;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task, $user)
    {
        $this->task = $task;
        $this->user = $user;
        $this->relatedItem = $this->getRelatedItem();
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Task Reminder: ' . $this->task->name)
            ->view('emails.task-reminder')
            ->with([
                'task' => $this->task,
                'user' => $this->user,
                'relatedItem' => $this->relatedItem,
            ]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    private function getRelatedItem()
    {
        if ($this->task->related_type === 'lead') {
            return \App\Models\Lead::find($this->task->related_id);
        } elseif ($this->task->related_type === 'deal') {
            return \App\Models\Deal::find($this->task->related_id);
        }
        return null;
    }
}
