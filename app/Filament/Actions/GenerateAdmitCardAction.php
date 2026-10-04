<?php

namespace App\Filament\Actions;

use App\Enums\ExamEntryStatus;
use App\Models\ExamStudentEntry;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class GenerateAdmitCardAction
{
    public static function make(?string $name = 'generateAdmitCard'): Action
    {
        return Action::make($name)
            ->label('Generate Admit Card')
            ->icon('heroicon-o-identification')
            ->color('primary')
            ->requiresConfirmation()
            ->action(function (ExamStudentEntry $record) {
                $record->generateAdmitCardData();
                $record->update(['status' => ExamEntryStatus::ADMIT_CARD_GENERATED]);

                Notification::make()
                    ->title('Admit Card Generated')
                    ->body("Admit card generated for roll no {$record->roll_no}")
                    ->success()
                    ->send();
            });
    }
}