<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ModuleCollectionResource extends ResourceCollection
{
    /** true ⇒ include per-student progress fields */
    protected bool $forStudent;

    public function __construct($resource, bool $forStudent = false)
    {
        parent::__construct($resource);
        $this->forStudent = $forStudent;
    }

    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(fn ($m) => [
                'module_id'   => $m->module_id,
                'title'       => $m->title,
                'description' => $m->description,
                'position'    => $m->position,
                'progress'    => $this->forStudent
                    ? optional($m->progress)->only(['completed','score'])
                    : null,
            ]),
        ];
    }
}
