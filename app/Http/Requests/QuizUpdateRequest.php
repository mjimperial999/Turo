<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuizUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'activity_id'          => 'required|exists:activity,activity_id',
            'activity_name'        => 'sometimes|string|max:255',
            'activity_description' => 'sometimes|nullable|string',
            'unlock_date'          => 'sometimes|date',
            'deadline_date'        => 'sometimes|date|after:unlock_date',
            'quiz_type_id'         => 'sometimes|in:1,2',
            // updating questions is optional – front-end can send the same structure
            'questions'            => 'sometimes|array|min:1',
            'questions.*.text'     => 'required_with:questions|string',
            'questions.*.correct'  => 'required_with:questions|integer|min:0',
            'questions.*.options'  => 'required_with:questions|array|min:1|max:4',
            'questions.*.options.*'=> 'required_with:questions|string',
            'questions.*.image'    => 'nullable|image|max:2048',
        ];
    }
}