<?php

namespace App\Notifications;

use App\Models\ExamMark;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MarksUpdated extends Notification
{
    use Queueable;

    public function __construct(public ExamMark $mark) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Marks Updated',
            'message' => "Marks entered for {$this->mark->examSubject?->subject?->name}",
            'mark_id' => $this->mark->id,
        ];
    }
}