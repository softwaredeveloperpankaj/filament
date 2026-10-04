<?php

namespace App\Services;

use App\Enums\ResultStatus;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamStudentEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RankCalculationService
{
    /**
     * Calculate and assign section, class, and branch ranks for an Exam.
     */
    public function calculateRanksForExam(Exam $exam): void
    {
        DB::transaction(function () use ($exam) {
            $allResults = ExamResult::with('entry')
                ->where('exam_id', $exam->id)
                ->get();

            $this->resetRanksForCollection($allResults);

            foreach ($allResults as $result) {
                $result->saveQuietly();
            }

            $results = $allResults
                ->filter(fn ($result) => $result->status === ResultStatus::PUBLISHED)
                ->filter(fn ($result) => (bool) $result->is_passed === true);

            if ($results->isEmpty()) {
                return;
            }

            if ($results->isEmpty()) {
                return;
            }

            // 1. Calculate Rank in Class (all published students in this exam/class)
            $this->assignRanks($results, 'rank_in_class');

            // 2. Calculate Rank in Section (group by section_id)
            $bySection = $results->groupBy(fn($r) => $r->entry?->section_id ?? 0);
            foreach ($bySection as $sectionResults) {
                $this->assignRanks($sectionResults, 'rank_in_section');
            }

            // 3. Calculate Rank in Branch (all published students in the same branch)
            $this->assignRanks($results, 'rank_in_branch');

            foreach ($results as $result) {
                $result->saveQuietly();
            }
        });
    }

    public function resetRanksForCollection(Collection $results): void
    {
        foreach ($results as $result) {
            $result->rank_in_class = null;
            $result->rank_in_section = null;
            $result->rank_in_branch = null;
        }
    }

    /**
     * Assign standard competition ranks (1, 2, 2, 4) to a collection based on grand_total_obtained.
     */
    protected function assignRanks($collection, string $rankColumn): void
    {
        $sorted = $collection->sortByDesc(fn($item) => (float) $item->grand_total_obtained)
            ->values();

        $currentRank = 1;
        $previousScore = null;
        $skipped = 0;

        foreach ($sorted as $index => $item) {
            $score = (float) $item->grand_total_obtained;

            if ($index === 0) {
                $item->{$rankColumn} = 1;
                $previousScore = $score;
                $currentRank = 1;
                $skipped = 0;
                continue;
            }

            if ($score == $previousScore) {
                $item->{$rankColumn} = $currentRank;
                $skipped++;
            } else {
                $currentRank = $index + 1;
                $item->{$rankColumn} = $currentRank;
                $previousScore = $score;
                $skipped = 0;
            }
        }
    }
}