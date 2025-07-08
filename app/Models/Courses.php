<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courses extends Model
{

    protected $table = 'course';
    protected $primaryKey = 'course_id';
    protected $keyType = 'string';

    public $incrementing = false;
    public $timestamps   = false;


    protected $fillable = [
        'course_id',
        'course_code',
        'course_name',
        'course_description',
        'start_date',
        'end_date',
    ];

    public function modules()
    {
        return $this->hasMany(Modules::class, 'course_id');
    }

    public function image()
    {
        return $this->hasOne(CourseImage::class, 'course_id', 'course_id');
    }

    public function longquizzes()
    {
        return $this->hasMany(LongQuizzes::class, 'course_id');
    }

    public function screenings()
    {
        return $this->hasMany(Screening::class, 'course_id', 'course_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(
            Teachers::class,
            CourseSection::class,
            'course_id',
            'teacher_id',
            'course_id',
            'user_id'
        );
    }
}
