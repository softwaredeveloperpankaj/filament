<?php

namespace App\Events;

use App\Models\ExamMark;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MarksEntered
{
    use Dispatchable, SerializesModels;

    public function __construct(public ExamMark $mark) {}
}