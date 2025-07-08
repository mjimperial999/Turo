<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'success' => true,
            'id'      => $this->resource->module_id ?? $this->resource->getKey(),
            'message' => $this->additional['msg'] ?? null,
        ];
    }
}
