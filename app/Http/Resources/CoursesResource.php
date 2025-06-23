<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CoursesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->module_id,          // map DB column → API field
            'name'        => $this->module_name,
            'description' => $this->module_description,
        ];
    }
}