<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuleTeacherCollectionResource extends JsonResource
{
    public function toArray($req)
    {
        return [
            'module_id'    => $this->module_id,
            'module_name'  => $this->module_name,
            'module_descriptiom'  => $this->module_descriptiom,
            'image_blob'   => $this->moduleimage
                ? base64_encode($this->moduleimage->image)
                : null,
        ];
    }
}
