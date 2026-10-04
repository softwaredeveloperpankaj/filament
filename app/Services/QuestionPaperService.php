<?php

namespace App\Services;

use App\Models\ExamQuestionPaper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class QuestionPaperService
{
    /**
     * Generate HTML for a question paper.
     */
    public static function generateHtml(ExamQuestionPaper $paper): string
    {
        $exam = $paper->exam;
        $examSubject = $paper->examSubject;
        $questions = $paper->questions;
        $grouped = $questions->groupBy(fn($q) => $q->question_type?->value ?? 'general');

        return view('exams.question-paper', [
            'exam' => $exam,
            'examSubject' => $examSubject,
            'paper' => $paper,
            'questions' => $questions,
            'grouped' => $grouped,
        ])->render();
    }

    /**
     * Generate PDF for a single question paper.
     */
    public static function generatePdf(ExamQuestionPaper $paper): string
    {
        $html = self::generateHtml($paper);

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        return $pdf->output();
    }

    /**
     * Generate bulk PDF for multiple question papers.
     */
    public static function bulkPdf(iterable $papers): string
    {
        $pages = [];
        foreach ($papers as $paper) {
            $pages[] = self::generateHtml($paper);
        }

        $combinedHtml = implode("\n<div style='page-break-after: always;'></div>\n", $pages);

        $pdf = Pdf::loadHTML($combinedHtml)
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        return $pdf->output();
    }
}