<?php

namespace App\Filament\Actions;

use App\Models\OnlineExamAttempt;
use App\Services\OnlineExamSubmissionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class SubmitOnlineExamAction
{
    public static function make(?string $name = 'submitOnlineExam'): Action
    {
        return Action::make($name)
            ->label('Submit Exam')
            ->icon('heroicon-o-check-circle')
            ->color('warning')
            ->requiresConfirmation()
            ->action(function (OnlineExamAttempt $record, OnlineExamSubmissionService $service) {
                $service->submitAttempt($record, $record->answers ?? []);

                Notification::make()
                    ->title('Attempt Submitted')
                    ->body("Score: {$record->total_obtained}/{$record->total_maximum} ({$record->percentage}%)")
                    ->success()
                    ->send();
            });
    }
}