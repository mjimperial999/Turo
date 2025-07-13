<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LongQuizStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'course_id'           => 'required|exists:course,course_id',
            'long_quiz_name'      => 'required|string|max:255',
            'long_quiz_instructions' => 'nullable|string',
            'number_of_attempts'  => 'required|integer|min:1',
            'time_limit'          => 'required|integer|min:10',         // seconds
            'number_of_questions' => 'required|integer|min:1',
            'overall_points'      => 'required|integer|min:1',
            'has_answers_shown'   => 'required|boolean',
            'unlock_date'         => 'required|date',
            'deadline_date'       => 'required|date|after:unlock_date',
        ];
    }
}