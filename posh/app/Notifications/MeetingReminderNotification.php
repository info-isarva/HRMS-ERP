<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MeetingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $meeting;
    public $reminder;

    /**
     * Create a new notification instance.
     * Accept a Meeting and optional reminder context.
     */
    public function __construct($meeting, $reminder = null)
    {
        $this->meeting = $meeting;
        $this->reminder = $reminder;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $remindAt = $this->reminder ? $this->reminder->remind_at : null;
        $meetingLink = $this->meeting && $this->meeting->id ? route('meetings.show', $this->meeting->id) : '';

        $message = 'Reminder: Meeting "' . ($this->meeting->name ?? 'meeting') . '" is scheduled';
        if ($this->meeting && $this->meeting->start_at) {
            $message .= ' at ' . (\Carbon\Carbon::parse($this->meeting->start_at)->format('d M, Y H:i'));
        }
        if ($remindAt) {
            $message .= ' (reminder at ' . (\Carbon\Carbon::parse($remindAt)->format('d M, Y H:i')) . ')';
        }

        return [
            'meeting_id' => $this->meeting->id ?? null,
            'meeting_name' => $this->meeting->name ?? null,
            'description' => $this->meeting->description ?? null,
            'start_at' => $this->meeting->start_at ?? null,
            'location' => $this->meeting->location ?? null,
            'remind_at' => $remindAt ? (string) $remindAt : null,
            'message' => $message,
            'meeting_link' => $meetingLink,
            'is_current_meeting' => $this->meeting->id === optional($this->reminder)->meeting_id, // Ensure it's the current meeting
        ];
    }
}