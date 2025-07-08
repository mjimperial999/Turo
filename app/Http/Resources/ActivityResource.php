<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'activity_id' => $this->activity_id,
            'activity_name' => $this->activity_name,
            'activity_type'=> $this->activity_type,     // lecture / quiz / …
            'position'     => $this->position,
            // add any fields you need on the mobile side here
        ];
    }
}
