<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    public function toArray($req)
    {
        return [
            'module_id'   => $this->module_id,
            'title'       => $this->title,
            'description' => $this->description,
            'position'    => $this->position,
            'activities'  => ActivityResource::collection($this->whenLoaded('activities')),
            'progress'    => $this->when(isset($this->progress), fn() => [
                'completed' => $this->progress->completed,
                'score'     => $this->progress->score,
            ]),
        ];
    }
}
