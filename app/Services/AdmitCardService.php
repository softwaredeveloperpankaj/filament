<?php

namespace App\Services;

use App\Models\ExamStudentEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class AdmitCardService
{
    /**
     * Generate admit card HTML for a single entry
     */
    public static function generateHtml(ExamStudentEntry $entry): string
    {
        $data = $entry->admit_card_data ?? $entry->generateAdmitCardData();

        return view('filament.exams.admit-card', [
            'entry' => $entry,
            'data'  => $data,
        ])->render();
    }

    /**
     * Generate admit cards for multiple entries (returns combined HTML)
     */
    public static function bulkHtml(Collection $entries): string
    {
        $cards = $entries->map(fn($entry) => self::generateHtml($entry))->join("\n<hr class='page-break'>\n");

        return view('filament.exams.admit-card-bulk', [
            'cards' => $cards,
        ])->render();
    }

    /**
     * Generate PDF for single entry (requires dompdf or similar)
     */
    public static function generatePdf(ExamStudentEntry $entry): string
    {
        $html = self::generateHtml($entry);

        // Option 1: Using barryvdh/laravel-dompdf
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a5', 'portrait')
            ->setWarnings(false);

        return $pdf->output();
    }

    /**
     * Generate bulk PDF
     */
    public static function bulkPdf(Collection $entries): string
    {
        $html = self::bulkHtml($entries);

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a5', 'portrait')
            ->setWarnings(false);

        return $pdf->output();
    }

    /**
     * Mark entries as printed (used by bulk action)
     */
    public static function markPrinted(Collection $entries): int
    {
        $count = 0;
        foreach ($entries as $entry) {
            if (!$entry->admit_card_printed) {
                $entry->markAdmitCardPrinted();
                $count++;
            }
        }
        return $count;
    }
}