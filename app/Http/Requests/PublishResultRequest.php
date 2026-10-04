<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublishResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'exists:exams,id'],
        ];
    }
}