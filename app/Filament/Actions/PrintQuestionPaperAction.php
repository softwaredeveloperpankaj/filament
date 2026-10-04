<?php

namespace App\Filament\Actions;

use App\Models\ExamQuestionPaper;
use App\Services\QuestionPaperService;
use Filament\Actions\Action;

class PrintQuestionPaperAction
{
    public static function make(?string $name = 'printQuestionPaper'): Action
    {
        return Action::make($name)
            ->label('Print Paper (PDF)')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->action(function (ExamQuestionPaper $record) {
                $pdf = QuestionPaperService::generatePdf($record);

                return response()->streamDownload(
                    fn () => print($pdf),
                    "question-paper-{$record->id}.pdf",
                    ['Content-Type' => 'application/pdf']
                );
            });
    }
}