<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'student_id' => $this->student_id,
            'student_name' => $this->student?->name,
            'grand_total_obtained' => $this->grand_total_obtained,
            'grand_total_maximum' => $this->grand_total_maximum,
            'overall_percentage' => $this->overall_percentage,
            'overall_grade' => $this->overall_grade,
            'rank_in_class' => $this->rank_in_class,
            'rank_in_section' => $this->rank_in_section,
            'is_passed' => (bool) $this->is_passed,
            'status' => $this->status,
            'subject_wise_breakdown' => $this->subject_wise_breakdown,
        ];
    }
}