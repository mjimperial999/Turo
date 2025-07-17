<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    public function toArray($r): array
    {
        return [
            'module_id'          => $this->module_id,
            'module_name'        => $this->module_name,
            'module_description' => $this->module_description,
            'image_blob'         => $this->image?->image
                                    ? base64_encode($this->moduleimage->image)
                                    : null
                
        ];
    }
}
