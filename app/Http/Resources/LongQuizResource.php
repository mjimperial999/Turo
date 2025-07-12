<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LongQuizResource extends JsonResource
{
    public function toArray($req)
    {

        return [
            'long_quiz_id'            => $this->long_quiz_id,
            'course_id'               => $this->course_id,
            'long_quiz_name'          => $this->long_quiz_name,
            'long_quiz_instructions'  => $this->long_quiz_instructions,
            'unlock_date'             => $this->unlock_date,
            'deadline_date'           => $this->deadline_date,
            'number_of_attempts'      => (int) $this->number_of_attempts,
            'quiz_type_name'          => 'LONG',
            'time_limit'              => (int) $this->time_limit,          // seconds or minutes—whichever you store
            'number_of_questions'     => (int) $this->number_of_questions,
            'overall_points'          => (int) $this->overall_points,
            'has_answers_shown'       => (int) $this->has_answers_shown,
        ];
    }
}
