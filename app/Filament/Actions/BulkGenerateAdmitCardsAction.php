<?php

namespace App\Filament\Actions;

use App\Enums\ExamEntryStatus;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class BulkGenerateAdmitCardsAction
{
    public static function make(?string $name = 'bulkGenerateAdmitCards'): BulkAction
    {
        return BulkAction::make($name)
            ->label('Generate Admit Cards')
            ->icon('heroicon-o-identification')
            ->color('primary')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                $count = 0;
                foreach ($records as $record) {
                    $record->generateAdmitCardData();
                    $record->update(['status' => ExamEntryStatus::ADMIT_CARD_GENERATED]);
                    $count++;
                }

                Notification::make()
                    ->title('Admit Cards Generated')
                    ->body("Generated admit cards for {$count} students.")
                    ->success()
                    ->send();
            });
    }
}