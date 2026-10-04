<?php

namespace App\Observers;

use App\Models\ExamResult;
use App\Services\MarksCalculationService;

class ExamResultObserver
{
    /**
     * Handle the ExamResult "saving" event.
     */
    public function saving(ExamResult $result): void
    {
        if ($result->grand_total_maximum > 0) {
            $result->overall_percentage = round(($result->grand_total_obtained / $result->grand_total_maximum) * 100, 2);
            $service = new MarksCalculationService();
            $result->overall_grade = $service->calculateGrade($result->overall_percentage);
        }
    }
}