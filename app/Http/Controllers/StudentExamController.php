<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamStudentEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentExamController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $exams = Exam::where('status', '!=', 'draft')->orderByDesc('start_date')->get();

        return view('welcome', ['exams' => $exams]);
    }

    public function myExams(Request $request)
    {
        $user = Auth::user();
        $entries = ExamStudentEntry::with(['exam.subjects.subject'])
            ->limit(20)
            ->get();

        return response()->json([
            'entries' => $entries,
        ]);
    }
}