<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LectureStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'module_id'            => 'required|exists:module,module_id',
            'activity_name'        => 'required|string|max:255',
            'activity_description' => 'nullable|string',
            'unlock_date'          => 'required|date',
            'deadline_date'        => 'required|date|after:unlock_date',
            'file'                 => 'nullable|mimes:pdf,mp4|max:10240',
        ];
    }
}