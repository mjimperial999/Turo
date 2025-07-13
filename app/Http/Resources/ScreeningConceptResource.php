<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScreeningConceptResource extends JsonResource
{
    public function toArray($r)
    {
        return [
            'concept_id'   => $this->screening_concept_id,
            'concept_name' => $this->concept_name,
            'passing'      => (int) $this->passing_score,
            'topics'       => ScreeningTopicResource::collection($this->topics),
        ];
    }
}

class ScreeningTopicResource extends JsonResource
{
    public function toArray($r)
    {
        return [
            'topic_id'   => $this->screening_topic_id,
            'topic_name' => $this->topic_name,
            'questions'  => ScreeningQuestionResource::collection($this->questions),
        ];
    }
}

class ScreeningQuestionResource extends JsonResource
{
    public function toArray($r)
    {
        $typeName = match ($this->question_type_id) {
            1 => 'MULTIPLE_CHOICE',
            2 => 'SHORT_ANSWER',
            default => 'OTHER',
        };

        return [
            'question_id'  => $this->screening_question_id,
            'question_text' => $this->question_text,
            'question_image' => optional($this->image?->image)
                                ? base64_encode($this->image?->image)
                                : null,
            'type_name'    => $typeName,
            'score'        => (int) $this->score,
            'options'      => ScreeningOptionResource::collection($this->options),
        ];
    }
}

class ScreeningOptionResource extends JsonResource
{
    public function toArray($r)
    {
        return [
            'option_id'  => $this->screening_option_id,
            'option_txt' => $this->option_text,
            'is_correct' => (int) $this->is_correct,
        ];
    }
}
