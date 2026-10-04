<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ExamStatsWidget;
use App\Filament\Widgets\LiveExamMonitorWidget;
use App\Filament\Widgets\ResultPublicationWidget;
use App\Filament\Widgets\UpcomingExamsWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ExamDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';
    protected static ?int $navigationSort = 0;
    protected static ?string $title = 'Exam Operations Dashboard';

    protected string $view = 'filament.pages.exam-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            ExamStatsWidget::class,
            LiveExamMonitorWidget::class,
            UpcomingExamsWidget::class,
            ResultPublicationWidget::class,
        ];
    }
}