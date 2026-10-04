<?php

namespace App\Filament\Actions;

use App\Models\Exam;
use App\Services\ResultGenerationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class CalculateResultAction
{
    public static function make(?string $name = 'calculateResult'): Action
    {
        return Action::make($name)
            ->label('Calculate Results')
            ->icon('heroicon-o-calculator')
            ->color('warning')
            ->requiresConfirmation()
            ->action(function (Exam $record, ResultGenerationService $service) {
                $count = $service->generateResultsForExam($record);

                Notification::make()
                    ->title('Results Calculated')
                    ->body("Calculated results and ranks for {$count} student entries.")
                    ->success()
                    ->send();
            });
    }
}