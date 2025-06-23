<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courses extends Model
{
    protected $table = 'course'; // Name of The Table
    protected $primaryKey = 'course_id'; // Name of The Primary Key
    public $timestamps = false;

    protected $fillable = [
        'course_code',
        'course_name',
        'teacher_id',
        'course_description',
        'course_picture',
        'start_date',
        'end_date',
    ];

    public function modules()
    {
        return $this->hasMany(Modules::class, 'course_id');
    }

    public function image()
    {
        return $this->hasOne(CourseImage::class, 'course_id');
    }

    public function longquizzes()
    {
        return $this->hasMany(LongQuizzes::class, 'course_id');
    }
}
