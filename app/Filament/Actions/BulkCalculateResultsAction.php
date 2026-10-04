<?php

namespace App\Filament\Actions;

use App\Services\ResultGenerationService;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class BulkCalculateResultsAction
{
    public static function make(?string $name = 'bulkCalculateResults'): BulkAction
    {
        return BulkAction::make($name)
            ->label('Calculate Results')
            ->icon('heroicon-o-calculator')
            ->color('warning')
            ->requiresConfirmation()
            ->action(function (Collection $records, ResultGenerationService $service) {
                $totalCalculated = 0;
                foreach ($records as $exam) {
                    $totalCalculated += $service->generateResultsForExam($exam);
                }

                Notification::make()
                    ->title('Results Calculated')
                    ->body("Calculated results for {$totalCalculated} student entries.")
                    ->success()
                    ->send();
            });
    }
}