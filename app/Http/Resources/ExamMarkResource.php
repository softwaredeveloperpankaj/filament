<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamMarkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'exam_subject_id' => $this->exam_subject_id,
            'subject_name' => $this->examSubject?->subject?->name,
            'theory_obtained' => $this->theory_obtained,
            'practical_obtained' => $this->practical_obtained,
            'internal_obtained' => $this->internal_obtained,
            'total_obtained' => $this->total_obtained,
            'total_maximum' => $this->total_maximum,
            'percentage' => $this->percentage,
            'grade' => $this->grade,
            'is_passed' => (bool) $this->is_passed,
            'is_locked' => (bool) $this->is_locked,
        ];
    }
}