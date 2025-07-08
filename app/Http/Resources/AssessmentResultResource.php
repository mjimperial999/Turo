<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResultResource extends JsonResource
{
    public function toArray($req)
    {
        return [
            'result_id'        => $this->result_id,
            'module_id'        => $this->module_id,
            'score_percentage' => (double) $this->score_percentage,
            'date_taken'       => $this->date_taken,              // already Y-m-d H:i:s
            'attempt_number'   => (int)    $this->attempt_number,
            'earned_points'    => (int)    $this->earned_points,
            'answers'          => AnswerResource::collection($this->answers),
            'is_kept'          => (int)    $this->is_kept,
        ];
    }
}
