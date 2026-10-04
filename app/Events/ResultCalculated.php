<?php

namespace App\Events;

use App\Models\ExamResult;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResultCalculated
{
    use Dispatchable, SerializesModels;

    public function __construct(public ExamResult $result) {}
}