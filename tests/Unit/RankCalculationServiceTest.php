<?php

namespace Tests\Unit;

use App\Enums\ResultStatus;
use App\Models\ExamResult;
use App\Services\RankCalculationService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class RankCalculationServiceTest extends TestCase
{
    public function test_it_clears_stale_ranks_before_recalculating(): void
    {
        $service = new RankCalculationService();

        $results = collect([
            new ExamResult(['grand_total_obtained' => 90, 'status' => ResultStatus::PUBLISHED, 'is_passed' => true, 'rank_in_class' => 99, 'rank_in_section' => 99, 'rank_in_branch' => 99]),
            new ExamResult(['grand_total_obtained' => 80, 'status' => ResultStatus::PUBLISHED, 'is_passed' => false, 'rank_in_class' => 77, 'rank_in_section' => 77, 'rank_in_branch' => 77]),
        ]);

        $service->resetRanksForCollection($results);

        foreach ($results as $result) {
            $this->assertNull($result->rank_in_class);
            $this->assertNull($result->rank_in_section);
            $this->assertNull($result->rank_in_branch);
        }
    }

    public function test_it_assigns_competitive_ranks_to_valid_results(): void
    {
        $service = new RankCalculationService();
        $results = collect([
            new ExamResult(['grand_total_obtained' => 95]),
            new ExamResult(['grand_total_obtained' => 95]),
            new ExamResult(['grand_total_obtained' => 80]),
        ]);

        $method = new \ReflectionMethod(RankCalculationService::class, 'assignRanks');
        $method->setAccessible(true);
        $method->invoke($service, $results, 'rank_in_class');

        $this->assertSame(1, $results[0]->rank_in_class);
        $this->assertSame(1, $results[1]->rank_in_class);
        $this->assertSame(3, $results[2]->rank_in_class);
    }
}
