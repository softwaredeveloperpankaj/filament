<?php

namespace App\Filament\Widgets;

use App\Enums\AttemptStatus;
use App\Models\OnlineExamAttempt;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LiveExamMonitorWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $inProgress = OnlineExamAttempt::where('status', AttemptStatus::IN_PROGRESS)->count();
        $submitted = OnlineExamAttempt::whereIn('status', [AttemptStatus::SUBMITTED, AttemptStatus::AUTO_SUBMITTED])->count();
        $notStarted = OnlineExamAttempt::where('status', AttemptStatus::NOT_STARTED)->count();

        return [
            Stat::make('Live Online Attempts', $inProgress)
                ->description('Currently taking exam')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('danger'),

            Stat::make('Completed Attempts', $submitted)
                ->description('Successfully submitted')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Pending Attempts', $notStarted)
                ->description('Not started yet')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('gray'),
        ];
    }
}