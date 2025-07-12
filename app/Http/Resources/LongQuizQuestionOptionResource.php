<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LongQuizQuestionOptionResource extends JsonResource
{
    public function toArray($req)
    {
        return [
            'option_id'   => $this->long_quiz_option_id,
            'option_text' => $this->option_text,
            'is_correct'  => (int) $this->is_correct,
        ];
    }
}