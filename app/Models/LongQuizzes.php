<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LongQuizzes extends Model
{
    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'course_id');
    }

    protected $table = 'longquiz'; // Name of The Table
    protected $primaryKey = 'long_quiz_id'; // Name of The Primary Key
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'course_id',
        'long_quiz_name',
        'long_quiz_instructions',
        'number_of_attempts',
        'time_limit',
        'number_of_questions',
        'overall_points',
        'has_answers_shown',
        'unlock_date',
        'deadline_date',
    ];

    public function longquizquestions()
    {
        return $this->hasMany(LongQuizQuestions::class, 'long_quiz_id');
    }
}
