<?php

namespace App\Filament\Widgets;

use App\Enums\ExamStatus;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamStudentEntry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExamStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Exams', Exam::count())
                ->description('All registered exams')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make('Active & Scheduled', Exam::whereIn('status', [ExamStatus::SCHEDULED, ExamStatus::ONGOING])->count())
                ->description('In-progress or upcoming')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Students Enrolled', ExamStudentEntry::count())
                ->description('Total student entries')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Results Published', Exam::where('status', ExamStatus::RESULTS_PUBLISHED)->count())
                ->description('Completed evaluations')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}