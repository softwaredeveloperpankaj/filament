<?php

namespace App\Filament\Actions;

use App\Models\ExamSubject;
use App\Services\ExamPaperBuilderService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class GenerateQuestionPaperAction
{
    public static function make(?string $name = 'generateQuestionPaper'): Action
    {
        return Action::make($name)
            ->label('Generate Paper')
            ->icon('heroicon-o-document-text')
            ->color('success')
            ->requiresConfirmation()
            ->action(function (ExamSubject $record, ExamPaperBuilderService $service) {
                $paper = $service->buildPaper($record->exam, $record, ['auto_generate' => true]);

                Notification::make()
                    ->title('Paper Generated')
                    ->body("Question paper generated with {$paper->total_marks} total marks.")
                    ->success()
                    ->send();
            });
    }
}