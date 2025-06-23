<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseImage extends Model
{
    protected $table = 'course_image'; // Name of The Table
    protected $primaryKey = 'course_image_id'; // Name of The Primary Key
    public $timestamps = false;

    protected $fillable = [
        'course_image_id',
        'course_id',
        'image',
    ];

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }


}
