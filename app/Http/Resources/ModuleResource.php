<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->module_id, 
            'name'        => $this->module_name,
            'description' => $this->module_description,
        ];
    }
}
