<?php

namespace App\Events;

use App\Models\Exam;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Exam $exam) {}
}