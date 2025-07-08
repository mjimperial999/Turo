<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ActivityCollectionResource extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => ActivityResource::collection($this->collection),
        ];
    }
}
