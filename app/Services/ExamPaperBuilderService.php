<?php

namespace App\Services;

use App\Enums\DifficultyLevel;
use App\Enums\QuestionType;
use App\Models\Exam;
use App\Models\ExamQuestionPaper;
use App\Models\ExamSubject;
use App\Models\QuestionBank;
use App\Models\QuestionBankItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExamPaperBuilderService
{
    /**
     * Build or update an ExamQuestionPaper from configuration or QuestionBank.
     */
    public function buildPaper(Exam $exam, ExamSubject $examSubject, array $params = []): ExamQuestionPaper
    {
        $questionBankId = $params['question_bank_id'] ?? null;
        $sections = $params['sections'] ?? [];
        $instructions = $params['instructions'] ?? $exam->instructions;
        $isShuffled = $params['is_shuffled'] ?? false;
        $selectedQuestions = $params['selected_questions'] ?? [];

        // If automatic generation requested with structure
        if (empty($selectedQuestions) && $questionBankId && !empty($params['auto_generate'])) {
            $bank = QuestionBank::findOrFail($questionBankId);
            $selectedQuestions = $this->autoSelectQuestions(
                $bank,
                $params['structure'] ?? [],
                $examSubject->theory_marks ?? $examSubject->max_marks
            );
        }

        $totalMarks = $this->calculateTotalMarks($selectedQuestions);

        return ExamQuestionPaper::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'exam_subject_id' => $examSubject->id,
            ],
            [
                'question_bank_id' => $questionBankId,
                'selected_questions' => $selectedQuestions,
                'total_marks' => $totalMarks,
                'sections' => $sections,
                'instructions' => $instructions,
                'is_shuffled' => $isShuffled,
                'generated_by' => Auth::id() ?? $exam->created_by,
            ]
        );
    }

    /**
     * Automatically pick questions from QuestionBank matching structure and target marks.
     */
    public function autoSelectQuestions(QuestionBank $questionBank, array $structure, int $targetMarks): array
    {
        $selected = [];
        $runningMarks = 0;

        // If no explicit structure, pick evenly across types and difficulties
        if (empty($structure)) {
            $items = QuestionBankItem::where('question_bank_id', $questionBank->id)
                ->where('is_active', true)
                ->inRandomOrder()
                ->get();

            foreach ($items as $item) {
                if ($runningMarks + $item->marks <= $targetMarks) {
                    $selected[$item->id] = $item->marks;
                    $runningMarks += $item->marks;
                }

                if ($runningMarks >= $targetMarks) {
                    break;
                }
            }

            return $selected;
        }

        // Structure example: [['type' => 'mcq', 'count' => 10, 'marks_each' => 1, 'difficulty' => 'easy'], ...]
        foreach ($structure as $sec) {
            $query = QuestionBankItem::where('question_bank_id', $questionBank->id)
                ->where('is_active', true);

            if (!empty($sec['type'])) {
                $type = $sec['type'] instanceof QuestionType ? $sec['type']->value : $sec['type'];
                $query->where('question_type', $type);
            }

            if (!empty($sec['difficulty'])) {
                $diff = $sec['difficulty'] instanceof DifficultyLevel ? $sec['difficulty']->value : $sec['difficulty'];
                $query->where('difficulty', $diff);
            }

            $count = (int) ($sec['count'] ?? 5);
            $items = $query->inRandomOrder()->limit($count)->get();

            foreach ($items as $item) {
                $itemMarks = (int) ($sec['marks_each'] ?? $item->marks ?? 1);
                $selected[$item->id] = $itemMarks;
                $runningMarks += $itemMarks;
            }
        }

        return $selected;
    }

    /**
     * Calculate total marks from selected questions mapping [item_id => marks].
     */
    public function calculateTotalMarks(array $selectedQuestions): int
    {
        $total = 0;
        foreach ($selectedQuestions as $questionId => $marks) {
            if (is_numeric($marks)) {
                $total += (int) $marks;
            } else {
                $item = QuestionBankItem::find($questionId);
                $total += (int) ($item?->marks ?? 1);
            }
        }
        return $total;
    }

    /**
     * Validate whether paper total marks match exam subject requirements.
     */
    public function validatePaper(ExamQuestionPaper $paper): array
    {
        $examSubject = $paper->examSubject;
        $expectedMarks = $examSubject?->theory_marks ?? $examSubject?->max_marks ?? 100;
        $isValid = ($paper->total_marks === $expectedMarks);

        return [
            'is_valid' => $isValid,
            'current_marks' => $paper->total_marks,
            'expected_marks' => $expectedMarks,
            'difference' => $expectedMarks - $paper->total_marks,
            'question_count' => is_array($paper->selected_questions) ? count($paper->selected_questions) : 0,
        ];
    }
}