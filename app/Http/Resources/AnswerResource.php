<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
{
    public function toArray($req)
    {
        return [
            'question_id' => $this->question_id,
            'option_id'   => $this->option_id,
            'is_correct'  => (int) $this->is_correct,   // 0 / 1
        ];
    }
}
