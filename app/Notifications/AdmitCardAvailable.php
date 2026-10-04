<?php

namespace App\Notifications;

use App\Models\ExamStudentEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdmitCardAvailable extends Notification
{
    use Queueable;

    public function __construct(public ExamStudentEntry $entry) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Admit Card Generated',
            'message' => "Admit card is ready for exam {$this->entry->exam?->name}",
            'entry_id' => $this->entry->id,
            'exam_id' => $this->entry->exam_id,
        ];
    }
}