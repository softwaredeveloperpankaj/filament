<?php

namespace Tests\Unit;

use App\Enums\ResultStatus;
use App\Models\ExamResult;
use PHPUnit\Framework\TestCase;

class ExamResultModelTest extends TestCase
{
    public function test_draft_can_transition_to_published_and_cancelled(): void
    {
        $result = new ExamResult(['status' => ResultStatus::DRAFT]);

        $this->assertTrue($result->canTransitionTo(ResultStatus::PUBLISHED));
        $this->assertTrue($result->canTransitionTo(ResultStatus::CANCELLED));
        $this->assertTrue($result->canTransitionTo(ResultStatus::WITHHELD));
    }

    public function test_published_result_cannot_republish_or_become_draft(): void
    {
        $result = new ExamResult(['status' => ResultStatus::PUBLISHED]);

        $this->assertFalse($result->canTransitionTo(ResultStatus::PUBLISHED));
        $this->assertTrue($result->canTransitionTo(ResultStatus::WITHHELD));
        $this->assertTrue($result->canTransitionTo(ResultStatus::CANCELLED));
    }

    public function test_cancelled_result_is_locked_from_further_transition(): void
    {
        $result = new ExamResult(['status' => ResultStatus::CANCELLED]);

        $this->assertFalse($result->canTransitionTo(ResultStatus::PUBLISHED));
        $this->assertFalse($result->canTransitionTo(ResultStatus::WITHHELD));
        $this->assertFalse($result->canTransitionTo(ResultStatus::CANCELLED));
    }
}
