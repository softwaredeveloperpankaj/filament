<?php

namespace App\Listeners;

use App\Events\ResultCalculated;
use App\Services\RankCalculationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateStudentRank implements ShouldQueue
{
    public function handle(ResultCalculated $event): void
    {
        $service = new RankCalculationService();
        $service->calculateRanksForExam($event->result->exam);
    }
}