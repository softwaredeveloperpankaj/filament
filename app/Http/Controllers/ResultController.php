<?php

namespace App\Http\Controllers;

use App\Models\ExamResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function show(ExamResult $result): View
    {
        return view('exams.result-card', [
            'result' => $result,
        ]);
    }

    public function downloadPdf(ExamResult $result)
    {
        $html = view('exams.result-card', ['result' => $result])->render();
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait')->setWarnings(false);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"report-card-{$result->student_id}.pdf\"",
        ]);
    }
}