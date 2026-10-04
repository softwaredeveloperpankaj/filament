<?php

namespace App\Filament\Actions;

use App\Services\QuestionPaperService;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class BulkPrintQuestionPapersAction
{
    public static function make(?string $name = 'bulkPrintQuestionPapers'): BulkAction
    {
        return BulkAction::make($name)
            ->label('Bulk Print Papers')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->action(function (Collection $records) {
                $pdf = QuestionPaperService::bulkPdf($records);

                return response()->streamDownload(
                    fn () => print($pdf),
                    'question-papers-bulk.pdf',
                    ['Content-Type' => 'application/pdf']
                );
            });
    }
}