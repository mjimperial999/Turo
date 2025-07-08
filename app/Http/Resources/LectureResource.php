<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LectureResource extends JsonResource
{
    public function toArray($req)
    {
        return [
            'activity_name'        => $this->activity_name,
            'activity_description' => $this->activity_description,
            'file_url'             => $this->file_blob
                ? base64_encode($this->file_blob)
                : null,
        ];
    }
}
