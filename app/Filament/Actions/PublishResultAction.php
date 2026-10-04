<?php

namespace App\Filament\Actions;

use App\Models\Exam;
use App\Services\ResultGenerationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class PublishResultAction
{
    public static function make(?string $name = 'publishResult'): Action
    {
        return Action::make($name)
            ->label('Publish Results')
            ->icon('heroicon-o-megaphone')
            ->color('success')
            ->requiresConfirmation()
            ->action(function (Exam $record, ResultGenerationService $service) {
                $count = $service->publishResults($record, Auth::id());

                Notification::make()
                    ->title('Results Published')
                    ->body("Published results for {$count} students.")
                    ->success()
                    ->send();
            });
    }
}