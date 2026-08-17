<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class QuestionPaperController extends Controller
{
    public function show(Exam $exam, ExamSubject $examSubject): View
    {
        // Authorization: same branch only
        abort_unless(
            Auth::user()->hasRole('super_admin') || Auth::user()->branch_id === $exam->branch_id,
            403
        );

        $paper = $examSubject->questionPaper()->firstOrFail();

        $questions = $paper->questions; // uses your model accessor

        // Group questions by type for section-wise printing
        $grouped = $questions->groupBy(fn($q) => $q->question_type->value);

        return view('exams.question-paper', [
            'exam' => $exam,
            'examSubject' => $examSubject,
            'paper' => $paper,
            'questions' => $questions,
            'grouped' => $grouped,
        ]);
    }
}