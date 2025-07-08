<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource; 

class ModuleCollectionResource extends JsonResource   
{
    
    public function toArray($req)
    {
        return [
            'module_id'          => $this->module_id,
            'module_name'        => $this->module_name,
            'module_picture'     => $this->picture_blob
                                      ? base64_encode($this->picture_blob)
                                      : null,
            'module_description' => $this->module_description,
            'progress'           => (double) ($this->progress_value ?? 0),
        ];
    }
}
