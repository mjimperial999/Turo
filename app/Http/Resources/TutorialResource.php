<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TutorialResource extends JsonResource
{
    public function toArray($req)
    {
        return [
            'activity_name'        => $this->activity_name,
            'activity_description' => $this->activity_description,
            'video_url'             => $this->video_url
        ];
    }
}
