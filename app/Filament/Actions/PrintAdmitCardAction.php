<?php

namespace App\Filament\Actions;

use App\Models\ExamStudentEntry;
use App\Services\AdmitCardService;
use Filament\Actions\Action;

class PrintAdmitCardAction
{
    public static function make(?string $name = 'printAdmitCard'): Action
    {
        return Action::make($name)
            ->label('Print Admit Card')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->action(function (ExamStudentEntry $record) {
                $record->markAdmitCardPrinted();
                $pdf = AdmitCardService::generatePdf($record);

                return response()->streamDownload(
                    fn () => print($pdf),
                    "admit-card-{$record->roll_no}.pdf",
                    ['Content-Type' => 'application/pdf']
                );
            });
    }
}