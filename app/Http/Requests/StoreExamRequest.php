<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'branch_class_id' => ['required', 'exists:branch_classes,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'name' => ['required', 'string', 'max:255'],
            'mode' => ['required', 'in:online,offline'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}