<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModuleUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'module_id'          => 'required|exists:module,module_id',
            'module_name'        => 'sometimes|string|max:255',
            'module_description' => 'sometimes|nullable|string',
            'image'              => 'sometimes|nullable|image|max:2048',
        ];
    }
}
