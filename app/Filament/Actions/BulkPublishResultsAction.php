<?php

namespace App\Filament\Actions;

use App\Services\ResultGenerationService;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class BulkPublishResultsAction
{
    public static function make(?string $name = 'bulkPublishResults'): BulkAction
    {
        return BulkAction::make($name)
            ->label('Publish Results')
            ->icon('heroicon-o-megaphone')
            ->color('success')
            ->requiresConfirmation()
            ->action(function (Collection $records, ResultGenerationService $service) {
                $count = 0;
                foreach ($records as $exam) {
                    $count += $service->publishResults($exam, Auth::id());
                }

                Notification::make()
                    ->title('Results Published')
                    ->body("Published {$count} student results across selected exams.")
                    ->success()
                    ->send();
            });
    }
}