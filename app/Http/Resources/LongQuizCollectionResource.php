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
            'unlock_date'           => $this->unlock_date,
            'deadline_date'         => $this->deadline_date,
        ];
    }
}
