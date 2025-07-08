<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuleStudentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'module_id'          => $this->module_id,
            'module_name'        => $this->module_name,
            'module_picture'     => $this->moduleimage
                                      ? base64_encode($this->moduleimage->image)
                                      : null,
            'module_description' => $this->module_description,
            'progress'           => (double) ($this->progress_value ?? 0),
            'is_Catch_Up'        => (int)    ($this->isCatchUp      ?? 0),
        ];
    }
}
