<?php

use App\Http\Controllers\AdmitCardController;
use App\Http\Controllers\OnlineExamController;
use App\Http\Controllers\QuestionPaperController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\StudentExamController;
use App\Models\Student;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/filament/bulk-forms/download', function () {
    $path = decrypt(request('path'));

    abort_unless(Storage::disk('local')->exists($path), 404);

    return Storage::disk('local')->download($path);
})
->middleware(['web', 'auth'])
->name('filament.bulk-forms.download');

Route::middleware(['web', 'auth'])->get('/form-template-exports/download', function () {
    $path = decrypt(request('file'));

    abort_unless(
        str_starts_with($path, 'form_exports/'),
        403
    );

    abort_unless(Storage::disk('local')->exists($path), 404);

    return Storage::disk('local')->download($path, basename($path));
})->name('form-template-exports.download');

Route::get('/students/{student}/print', function (Student $student) {
    $template_layout_name = $student->formTemplate?->form_layout ?? 'default';
    return view("filament.print-admission-form.{$template_layout_name}", ['student' => $student]);
})->name('students.admission-form-print')->middleware(['auth']);

Route::middleware(['auth', 'verified'])->group(function () {
    // Question paper
    Route::get('exams/{exam}/question-paper/{examSubject}', [QuestionPaperController::class, 'show'])
        ->name('exams.question-paper');

    // Admit cards
    Route::get('admit-cards/{entry}/download', [AdmitCardController::class, 'download'])
        ->name('admit-cards.download');
    Route::get('admit-cards/{entry}/print', [AdmitCardController::class, 'print'])
        ->name('admit-cards.print');
    Route::get('exams/{exam}/admit-cards/bulk', [AdmitCardController::class, 'bulk'])
        ->name('exams.admit-cards.bulk');

    // Results & Report Cards
    Route::get('results/{result}/print', [ResultController::class, 'show'])
        ->name('results.show');
    Route::get('results/{result}/download', [ResultController::class, 'downloadPdf'])
        ->name('results.download');

    // Student Exam Portal
    Route::get('student/exams', [StudentExamController::class, 'index'])
        ->name('student.exams.index');
    Route::get('student/my-exams', [StudentExamController::class, 'myExams'])
        ->name('student.exams.my-exams');

    // Online Exam Attempts
    Route::post('online-exams/{attempt}/start', [OnlineExamController::class, 'start'])
        ->name('online-exams.start');
    Route::post('online-exams/{attempt}/save-answer', [OnlineExamController::class, 'saveAnswer'])
        ->name('online-exams.save-answer');
    Route::post('online-exams/{attempt}/submit', [OnlineExamController::class, 'submit'])
        ->name('online-exams.submit');
});