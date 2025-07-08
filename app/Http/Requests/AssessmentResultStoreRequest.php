<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssessmentResultStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'student_id'      => 'required|exists:student,user_id',
            'activity_id'     => 'required|exists:activity,activity_id',
            'score_percentage'=> 'required|numeric|min:0|max:100',
            'earned_points'   => 'required|integer|min:0',
            'answers'         => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:question,question_id',
            'answers.*.option_id'   => 'required|exists:option,option_id',
            'answers.*.is_correct'  => 'required|boolean',
        ];
    }
}
