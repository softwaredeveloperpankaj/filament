<?php

namespace Tests\Unit;

use App\Enums\ResultStatus;
use App\Models\ExamResult;
use App\Services\ResultGenerationService;
use PHPUnit\Framework\TestCase;

class ResultGenerationServiceTest extends TestCase
{
    public function test_it_preserves_existing_status_when_recalculating(): void
    {
        $service = new ResultGenerationService();
        $existing = new ExamResult(['status' => ResultStatus::PUBLISHED]);

        $this->assertSame(ResultStatus::PUBLISHED, $service->determinePersistedStatus($existing));
    }

    public function test_it_defaults_to_draft_for_new_results(): void
    {
        $service = new ResultGenerationService();

        $this->assertSame(ResultStatus::DRAFT, $service->determinePersistedStatus(null));
    }
}
