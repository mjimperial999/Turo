<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LongQuizCollectionResource extends JsonResource
{
    public function toArray($req)
    {
        return [
            'course_id'             => $this->course_id,
            'long_quiz_id'          => $this->long_quiz_id,
            'long_quiz_name'        => $this->long_quiz_name,
            'unlock_date'           => optional($this->unlock_date)->toAtomString(),
            'deadline_date'         => optional($this->deadline_date)->toAtomString(),
        ];
    }
}
