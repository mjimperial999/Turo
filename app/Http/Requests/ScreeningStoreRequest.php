<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScreeningStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'course_id'           => 'required|exists:course,course_id',
            'screening_name'      => 'required|string|max:255',
            'screening_instructions' => 'nullable|string',
            'time_limit'          => 'required|integer|min:60',
            'number_of_questions' => 'required|integer|min:1',
        ];
    }
}