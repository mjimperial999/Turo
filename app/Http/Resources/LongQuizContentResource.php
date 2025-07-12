<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LongQuizContentResource extends JsonResource
{
    public function toArray($req)
    {
        $typeName = match ($this->question_type_id) {
            1 => 'MULTIPLE_CHOICE',
            2 => 'SHORT_ANSWER',
            default => 'OTHER',
        };

        return [
            'question_id'      => $this->long_quiz_question_id,
            'question_text'    => $this->question_text,
            'question_image'   => $this->question_blob ? base64_encode($this->question_blob) : null,
            'type_name'        => $typeName,
            'score'            => (int) $this->score,
            'options'          => LongQuizQuestionOptionResource::collection($this->longquizoptions),
        ];
    }
}
