<?php

namespace App\Events;

use App\Models\ExamStudentEntry;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdmitCardGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(public ExamStudentEntry $entry) {}
}