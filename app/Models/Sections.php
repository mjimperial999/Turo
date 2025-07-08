<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sections extends Model
{
    protected $table      = 'section';
    protected $primaryKey = 'section_id';
    public    $incrementing = false;
    public    $timestamps   = false;
    protected $keyType      = 'string';

    protected $fillable = [
        'section_id',
        'section_name'
    ];

    public function students()
    {
        return $this->hasMany(Students::class, 'section_id', 'section_id');
    }

    public function courses()
    {
        return $this->belongsToMany(
            Courses::class,
            CourseSection::class,    // pivot model
            'section_id',
            'course_id',
            'section_id',
            'course_id'
        )->withPivot('teacher_id');
    }

    public function courseLinks()
    {
        return $this->hasMany(CourseSection::class, 'course_id');
    }
}
