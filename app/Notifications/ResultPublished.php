<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResultPublished extends Notification
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
            'title' => 'Results Published',
            'message' => "Results have been published for {$this->exam->name}",
            'exam_id' => $this->exam->id,
        ];
    }
}