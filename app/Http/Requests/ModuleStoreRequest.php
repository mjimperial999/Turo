<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModuleStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'course_id'          => 'required|exists:course,course_id',
            'module_name'        => 'required|string|max:255',
            'module_description' => 'nullable|string',
            'image'              => 'nullable|image|max:2048',
        ];
    }
}
