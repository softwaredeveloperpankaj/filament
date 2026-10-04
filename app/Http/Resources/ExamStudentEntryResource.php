<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamStudentEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'student_id' => $this->student_id,
            'student_name' => $this->student?->name,
            'roll_no' => $this->roll_no,
            'status' => $this->status,
            'admit_card_printed' => (bool) $this->admit_card_printed,
        ];
    }
}