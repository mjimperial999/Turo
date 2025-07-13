<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScreeningCollectionResource extends JsonResource
{
    public function toArray($req)
    {

        return [
            'screening_id'          => $this->screening_id,
            'course_id'             => $this->course_id,
            'screening_name'        => $this->screening_name,
            'screening_image'     => optional($this->image?->image)
                                      ? base64_encode($this->image?->image)
                                      : null,
        ];
    }
}
