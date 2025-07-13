<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LectureUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'activity_id'          => 'required|exists:activity,activity_id',
            'activity_name'        => 'sometimes|string|max:255',
            'activity_description' => 'sometimes|nullable|string',
            'unlock_date'          => 'sometimes|date',
            'deadline_date'        => 'sometimes|date|after:unlock_date',
            'file'                 => 'sometimes|nullable|mimes:pdf,mp4|max:10240',
        ];
    }
}