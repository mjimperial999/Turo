<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityCollectionResource extends JsonResource
{
    public function toArray($req)
    {
        $quizName = match ($this->quiz_type_id) {
            1       => 'SHORT',
            2       => 'PRACTICE',
            default => null,
        };

        return [
            'module_id'           => $this->module_id,
            'activity_id'         => $this->activity_id,
            'activity_type'       => $this->activity_type,
            'activity_name'       => $this->activity_name,
            'quiz_type_name'      => $quizName,          // null for lectures/tutorials
            'activity_description'=> $this->activity_description,
            'unlock_date'         => $this->unlock_date,
            'deadline_date'       => $this->deadline_date,
        ];
    }
}
