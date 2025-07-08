<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModuleStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'module_id'   => 'required|uuid',
            'course_id'   => 'required|uuid|exists:courses,course_id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'position'    => 'required|integer',
        ];
    }
}
