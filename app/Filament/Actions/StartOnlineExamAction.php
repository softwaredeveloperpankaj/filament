<?php

namespace App\Filament\Actions;

use App\Enums\AttemptStatus;
use App\Models\OnlineExamAttempt;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class StartOnlineExamAction
{
    public static function make(?string $name = 'startOnlineExam'): Action
    {
        return Action::make($name)
            ->label('Start Exam')
            ->icon('heroicon-o-play')
            ->color('success')
            ->requiresConfirmation()
            ->action(function (OnlineExamAttempt $record) {
                $record->update([
                    'status' => AttemptStatus::IN_PROGRESS,
                    'started_at' => Carbon::now(),
                ]);

                Notification::make()
                    ->title('Exam Started')
                    ->success()
                    ->send();
            });
    }
}