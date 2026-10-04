<?php

namespace App\Filament\Actions;

use App\Models\Exam;
use Filament\Actions\Action;

class EnterMarksAction
{
    public static function make(?string $name = 'enterMarks'): Action
    {
        return Action::make($name)
            ->label('Enter Marks')
            ->icon('heroicon-o-pencil-square')
            ->color('primary')
            ->url(fn (Exam $record): string => route('filament.admin.resources.exam-marks.enter-marks', ['exam_id' => $record->id]));
    }
}