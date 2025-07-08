<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentScoreResource extends JsonResource
{
    /* Because some schemas do NOT store attempt_number, we
       generate it on the fly using the $this->additional index. */
    public function toArray($req)
    {
        return [
            'result_id'       => $this->result_id,
            'attempt_number'  => $this->additional['idx'] ?? $this->attempt_number ?? 1,
            'score_percentage'=> (double) $this->score_percentage,
            'date_taken'      => $this->date_taken,
        ];
    }

    /* Hook that lets the collection inject an array index */
    public function withCollection($req, $resources)
    {
        foreach ($resources as $idx => $item) {
            $item->additional['idx'] = $idx + 1;        // 1-based
        }
        return [];
    }
}
