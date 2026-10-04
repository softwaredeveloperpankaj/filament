<?php

namespace App\Filament\Actions;

use App\Models\ExamMark;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class LockMarksAction
{
    public static function make(?string $name = 'lockMarks'): Action
    {
        return Action::make($name)
            ->label(fn (ExamMark $record): string => $record->is_locked ? 'Unlock Marks' : 'Lock Marks')
            ->icon(fn (ExamMark $record): string => $record->is_locked ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
            ->color(fn (ExamMark $record): string => $record->is_locked ? 'warning' : 'danger')
            ->requiresConfirmation()
            ->action(function (ExamMark $record) {
                $record->update(['is_locked' => !$record->is_locked]);

                Notification::make()
                    ->title($record->is_locked ? 'Marks Locked' : 'Marks Unlocked')
                    ->success()
                    ->send();
            });
    }
}