<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentResult extends Model
{
    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id', 'user_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quizzes::class,'activity_id','activity_id');
    }

    protected $table = 'assessmentresult';
    protected $primaryKey = 'result_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'result_id',
        'student_id',
        'module_id',
        'activity_id',
        'score_percentage',
        'date_taken',
        'attempt_number',
        'tier_level_id',
        'earned_points',
        'is_kept',
    ];
}
