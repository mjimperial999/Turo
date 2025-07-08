<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    public function toArray($req)
    {
        /* map numeric → enum string */
        $quizTypeName = match ($this->quiz_type_id) {
            1       => 'SHORT',
            2       => 'PRACTICE',
            default => 'OTHER',
        };

        return [
            'activity_id'        => $this->activity_id,
            'module_name'        => $this->module_name,
            'activity_type'      => $this->activity_type,
            'activity_name'      => $this->activity_name,
            'activity_description'=> $this->activity_description,
            'unlock_date'        => $this->unlock_date,
            'deadline_date'      => $this->deadline_date,
            'number_of_attempts' => (int) $this->number_of_attempts,
            'quiz_type_name'     => $quizTypeName,
            'time_limit'         => (int) $this->time_limit,          // seconds or minutes—whichever you store
            'number_of_questions'=> (int) $this->number_of_questions,
            'overall_points'     => (int) $this->overall_points,
            'has_answers_shown'  => (int) $this->has_answers_shown,
        ];
    }
}
