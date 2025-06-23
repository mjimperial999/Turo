<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CoursesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'course_id' => $this->course_id,
            'course_code' => $this->course_code,
            'teacher_id' => $this->teacher_id,
            'course_description' => $this->course_description,
            'course_picture' => $this->course_picture,
            'course_name' => $this->course_name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'image' =>  $this->image
        ];
    }
}
