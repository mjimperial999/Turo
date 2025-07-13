<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScreeningUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'screening_id'          => 'required|exists:screening,screening_id',
            'screening_name'        => 'sometimes|string|max:255',
            'screening_instructions'=> 'sometimes|nullable|string',
            'time_limit'            => 'sometimes|integer|min:60',
            'number_of_questions'   => 'sometimes|integer|min:1',
        ];
    }
}