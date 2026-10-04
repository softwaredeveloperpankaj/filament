<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExamStartingSoon extends Notification
{
    use Queueable;

    public function __construct(public Exam $exam) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Exam Starting Soon',
            'message' => "Exam {$this->exam->name} starts on {$this->exam->start_date?->format('Y-m-d')}",
            'exam_id' => $this->exam->id,
        ];
    }
}