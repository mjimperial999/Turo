<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScreeningResultStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'student_id'     => 'required|exists:student,user_id',
            'screening_id'   => 'required|exists:screening,screening_id',
            'score_percentage' => 'required|numeric|min:0|max:100',
            'earned_points'    => 'required|integer|min:0',
            'answers'                       => 'required|array|min:1',
            'answers.*.question_id'         => 'required|exists:screeningquestion,screening_question_id',
            'answers.*.option_id'           => 'nullable|exists:screeningoption,screening_option_id',
            'answers.*.is_correct'          => 'required|integer|in:0,1',
        ];
    }
}
