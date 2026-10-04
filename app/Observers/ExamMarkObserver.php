<?php

namespace App\Observers;

use App\Models\ExamMark;
use App\Services\MarksCalculationService;

class ExamMarkObserver
{
    /**
     * Handle the ExamMark "saving" event.
     */
    public function saving(ExamMark $mark): void
    {
        $service = new MarksCalculationService();
        $service->calculateMark($mark);
    }
}