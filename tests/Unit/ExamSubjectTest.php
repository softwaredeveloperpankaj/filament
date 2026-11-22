<?php

namespace Tests\Unit;

use App\Models\ExamSubject;
use PHPUnit\Framework\Attributes\Test;

class ExamSubjectTest extends \Tests\TestCase
{
    #[Test]
    public function it_tracks_class_and_section_for_exam_subjects(): void
    {
        $fillable = (new ExamSubject())->getFillable();

        $this->assertContains('branch_class_id', $fillable);
        $this->assertContains('section_id', $fillable);
    }
}
