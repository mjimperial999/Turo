<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProgress extends Model
{
    public function student(){
        return $this->belongsTo(Students::class,'student_id','user_id');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class,'course_id','course_id');
    }

    protected $table = 'studentprogress'; // Name of The Table
    protected $primaryKey = 'student_id'; // Name of The Primary Key
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'student_id',
        'course_id',
        'total_points',
        'average_score',
        'score_percentage',
        'short_quiz_avg',
        'long_quiz_avg',
    ];

    public function moduleProgresses()
    {
        return $this->hasMany(ModuleProgress::class, 'student_id', 'student_id')
                    ->where('course_id', $this->course_id);
    }
}
