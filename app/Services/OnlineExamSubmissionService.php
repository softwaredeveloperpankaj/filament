<?php

namespace App\Services;

use App\Enums\AttemptStatus;
use App\Enums\MarksSource;
use App\Enums\QuestionType;
use App\Models\ExamMark;
use App\Models\ExamStudentEntry;
use App\Models\OnlineExamAttempt;
use App\Models\QuestionBankItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OnlineExamSubmissionService
{
    /**
     * Submit and auto-evaluate an online exam attempt.
     */
    public function submitAttempt(
        OnlineExamAttempt $attempt,
        array $answers,
        bool $isAutoSubmitted = false,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): OnlineExamAttempt {
        return DB::transaction(function () use ($attempt, $answers, $isAutoSubmitted, $ipAddress, $userAgent) {
            $paper = $attempt->examSubject?->questionPaper;
            $selectedQuestions = $paper?->selected_questions ?? [];
            $questionIds = !empty($selectedQuestions) ? array_keys($selectedQuestions) : array_keys($answers);

            $questions = QuestionBankItem::whereIn('id', $questionIds)->get()->keyBy('id');

            $autoScore = [];
            $totalObtained = 0;
            $totalMaximum = 0;
            $hasManualGrading = false;

            foreach ($questionIds as $qId) {
                $question = $questions->get($qId);
                if (!$question) {
                    continue;
                }

                $maxMarks = (int) ($selectedQuestions[$qId] ?? $question->marks ?? 1);
                $totalMaximum += $maxMarks;
                $studentAnswer = $answers[$qId] ?? null;

                $eval = $this->evaluateQuestionAnswer($question, $studentAnswer, $maxMarks);
                $autoScore[$qId] = $eval;

                if ($eval['is_manual']) {
                    $hasManualGrading = true;
                } else {
                    $totalObtained += $eval['obtained'];
                }
            }

            $percentage = ($totalMaximum > 0) ? round(($totalObtained / $totalMaximum) * 100, 2) : 0;
            $status = $isAutoSubmitted ? AttemptStatus::AUTO_SUBMITTED : AttemptStatus::SUBMITTED;

            $attempt->update([
                'status' => $status,
                'answers' => $answers,
                'auto_score' => $autoScore,
                'total_obtained' => $totalObtained,
                'total_maximum' => $totalMaximum,
                'percentage' => $percentage,
                'submitted_at' => Carbon::now(),
                'ip_address' => $ipAddress ?? $attempt->ip_address,
                'user_agent' => $userAgent ?? $attempt->user_agent,
            ]);

            // Synchronize to ExamMark record
            $this->syncToExamMark($attempt, $hasManualGrading);

            return $attempt->fresh();
        });
    }

    /**
     * Evaluate single question answer.
     */
    public function evaluateQuestionAnswer(QuestionBankItem $question, mixed $studentAnswer, int $maxMarks): array
    {
        $type = $question->question_type instanceof QuestionType ? $question->question_type->value : (string) $question->question_type;
        $correctAnswer = $question->correct_answer;
        $negativeMarks = (int) ($question->negative_marks ?? 0);

        // Subjective questions cannot be auto-scored
        if (in_array($type, [QuestionType::SHORT_ANSWER->value, QuestionType::LONG_ANSWER->value])) {
            return [
                'obtained' => 0,
                'max' => $maxMarks,
                'is_correct' => null,
                'is_manual' => true,
                'student_answer' => $studentAnswer,
                'feedback' => 'Pending teacher evaluation',
            ];
        }

        // Objective grading
        $isCorrect = false;

        if ($studentAnswer === null || $studentAnswer === '') {
            return [
                'obtained' => 0,
                'max' => $maxMarks,
                'is_correct' => false,
                'is_manual' => false,
                'student_answer' => null,
                'feedback' => 'Unanswered',
            ];
        }

        switch ($type) {
            case QuestionType::MCQ->value:
            case QuestionType::TRUE_FALSE->value:
                // Handle string, bool, or array match
                if (is_array($correctAnswer)) {
                    $isCorrect = in_array((string) $studentAnswer, array_map('strval', $correctAnswer));
                } else {
                    $isCorrect = (string) $studentAnswer === (string) $correctAnswer;
                }
                break;

            case QuestionType::FILL_BLANK->value:
                // Case-insensitive trimmed string match
                if (is_array($correctAnswer)) {
                    $normStudent = strtolower(trim((string) $studentAnswer));
                    $isCorrect = in_array($normStudent, array_map(fn($a) => strtolower(trim((string) $a)), $correctAnswer));
                } else {
                    $isCorrect = strcasecmp(trim((string) $studentAnswer), trim((string) $correctAnswer)) === 0;
                }
                break;

            case QuestionType::MATCHING->value:
                if (is_array($studentAnswer) && is_array($correctAnswer)) {
                    $isCorrect = ($studentAnswer == $correctAnswer);
                }
                break;
        }

        $obtained = $isCorrect ? $maxMarks : ($negativeMarks > 0 ? -$negativeMarks : 0);

        return [
            'obtained' => max(0, $obtained),
            'max' => $maxMarks,
            'is_correct' => $isCorrect,
            'is_manual' => false,
            'student_answer' => $studentAnswer,
            'feedback' => $isCorrect ? 'Correct' : 'Incorrect',
        ];
    }

    /**
     * Synchronize score from online exam attempt to ExamMark.
     */
    protected function syncToExamMark(OnlineExamAttempt $attempt, bool $hasManualGrading): void
    {
        $mark = ExamMark::firstOrNew([
            'exam_id' => $attempt->exam_id,
            'exam_student_entry_id' => $attempt->exam_student_entry_id,
            'exam_subject_id' => $attempt->exam_subject_id,
        ]);

        $mark->theory_obtained = $attempt->total_obtained;
        $mark->theory_maximum = $attempt->total_maximum;
        $mark->total_obtained = $attempt->total_obtained + ($mark->practical_obtained ?? 0) + ($mark->internal_obtained ?? 0);
        $mark->total_maximum = $attempt->total_maximum + ($mark->practical_maximum ?? 0) + ($mark->internal_maximum ?? 0);
        $mark->percentage = ($mark->total_maximum > 0) ? round(($mark->total_obtained / $mark->total_maximum) * 100, 2) : 0;
        $mark->source = $hasManualGrading ? MarksSource::ONLINE_MANUAL : MarksSource::ONLINE_AUTO;

        $examSubject = $attempt->examSubject;
        $passMarks = $examSubject?->pass_marks ?? 33;
        $mark->is_passed = ($mark->total_obtained >= $passMarks);

        // Grade calculation
        $markService = new MarksCalculationService();
        $mark->grade = $markService->calculateGrade($mark->percentage);

        $mark->save();
    }
}