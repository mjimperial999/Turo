<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LongQuizUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'long_quiz_id'          => 'required|exists:longquiz,long_quiz_id',
            'long_quiz_name'        => 'sometimes|string|max:255',
            'long_quiz_instructions'=> 'sometimes|nullable|string',
            'number_of_attempts'    => 'sometimes|integer|min:1',
            'time_limit'            => 'sometimes|integer|min:10',
            'number_of_questions'   => 'sometimes|integer|min:1',
            'overall_points'        => 'sometimes|integer|min:1',
            'has_answers_shown'     => 'sometimes|boolean',
            'unlock_date'           => 'sometimes|date',
            'deadline_date'         => 'sometimes|date|after:unlock_date',
        ];
    }
}