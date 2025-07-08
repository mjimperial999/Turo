<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teachers extends Model
{
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    protected $table      = 'teacher';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
    ];

    public function courses()
    {
        return $this->belongsToMany(
            Courses::class,
            'course_section',
            'teacher_id',
            'course_id' 
        );
    }

    public function courseSections()
    {
        return $this->hasMany(CourseSection::class, 'teacher_id', 'user_id')
            ->with(['course', 'section']);
    }
}
