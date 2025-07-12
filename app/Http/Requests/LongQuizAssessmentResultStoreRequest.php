<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LongQuizAssessmentResultStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'student_id'            => 'required|exists:student,user_id',
            'course_id'             => 'required|exists:course,course_id',
            'score_percentage'      => 'required|numeric|min:0|max:100',
            'earned_points'         => 'required|integer|min:0',
            'answers'               => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:longquiz_question,long_quiz_question_id',
            'answers.*.option_id'   => 'required|exists:longquiz_option,long_quiz_option_id',
            'answers.*.is_correct'  => 'required|integer|min:0|max:1',
        ];
    }
}
