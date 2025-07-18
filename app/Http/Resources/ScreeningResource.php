<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScreeningResource extends JsonResource
{
    public function toArray($req)
    {

        return [
            'screening_id'          => $this->screening_id,
            'course_id'             => $this->course_id,
            'screening_name'        => $this->screening_name,
            'screening_instructions'=> $this->screening_instructions,
            'time_limit'            => (int) $this->time_limit,
            'number_of_questions'   => (int) $this->number_of_questions,
        ];
    }
}
