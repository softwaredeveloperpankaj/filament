<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamStudentEntry;
use App\Services\AdmitCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdmitCardController extends Controller
{
    public function download(ExamStudentEntry $entry)
    {
        $entry->markAdmitCardPrinted();
        $pdf = AdmitCardService::generatePdf($entry);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"admit-card-{$entry->roll_no}.pdf\"",
        ]);
    }

    public function print(ExamStudentEntry $entry)
    {
        $entry->markAdmitCardPrinted();
        return AdmitCardService::generateHtml($entry);
    }

    public function bulk(Exam $exam, Request $request)
    {
        $query = ExamStudentEntry::where('exam_id', $exam->id);
        if ($request->has('section_id')) {
            $query->where('section_id', $request->query('section_id'));
        }

        $entries = $query->get();
        abort_if($entries->isEmpty(), 404, 'No enrolled candidates found.');

        AdmitCardService::markPrinted($entries);
        $pdf = AdmitCardService::bulkPdf($entries);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"bulk-admit-cards-exam-{$exam->id}.pdf\"",
        ]);
    }
}