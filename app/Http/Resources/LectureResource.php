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
            'unlock_date'          => optional($this->unlock_date)->toAtomString(),
            'deadline_date'        => optional($this->deadline_date)->toAtomString(),
            'file_url'             => $this->file_blob
                ? base64_encode($this->file_blob)
                : null,
        ];
    }
}
