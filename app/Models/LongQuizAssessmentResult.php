<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LongQuizAssessmentResult extends Model
{
    protected $table = 'long_assessmentresult';
    protected $primaryKey = 'result_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'result_id',
        'long_quiz_id',
        'student_id',
        'course_id',
        'score_percentage',
        'date_taken',
        'attempt_number',
        'earned_points',
        'is_kept',
    ];
}
