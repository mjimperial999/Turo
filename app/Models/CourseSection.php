<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseSection extends Model
{
    protected $table       = 'course_section';
    public    $incrementing = false;
    protected $keyType     = 'string';
    public    $timestamps  = false;

    protected $fillable = [
        'course_id',
        'section_id',
        'teacher_id'
    ];

    public function course ()
    {
        return $this->belongsTo(Courses::class , 'course_id' , 'course_id');
    }

    public function section()
    {
        return $this->belongsTo(Sections::class, 'section_id', 'section_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teachers::class,'teacher_id','user_id');
    }
}
