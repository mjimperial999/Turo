<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuizStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'module_id'            => 'required|exists:module,module_id',
            'activity_name'        => 'required|string|max:255',
            'activity_description' => 'nullable|string',
            'unlock_date'          => 'required|date',
            'deadline_date'        => 'required|date|after:unlock_date',
            'quiz_type_id'         => 'required|in:1,2',          // 1=SHORT  2=PRACTICE
            'questions'            => 'required|array|min:1',
            'questions.*.text'     => 'required|string',
            'questions.*.correct'  => 'required|integer|min:0',
            'questions.*.options'  => 'required|array|min:1|max:4',
            'questions.*.options.*'=> 'required|string',
            'questions.*.image'    => 'nullable|image|max:2048',
        ];
    }
}