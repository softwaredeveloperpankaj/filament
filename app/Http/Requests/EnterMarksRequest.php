<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnterMarksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'exists:exams,id'],
            'exam_subject_id' => ['required', 'exists:exam_subjects,id'],
            'marks' => ['required', 'array'],
            'marks.*.entry_id' => ['required', 'exists:exam_student_entries,id'],
            'marks.*.theory_obtained' => ['nullable', 'numeric', 'min:0'],
            'marks.*.practical_obtained' => ['nullable', 'numeric', 'min:0'],
            'marks.*.internal_obtained' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}