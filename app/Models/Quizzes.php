<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quizzes extends Model
{
    public function activity()
    {
        return $this->belongsTo(Activities::class, 'activity_id', 'activity_id');
    }

    protected $table = 'quiz'; // Name of The Table
    protected $primaryKey = 'activity_id'; // Name of The Primary Key
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'number_of_attempts',
        'quiz_type_id',
        'time_limit',
        'number_of_questions',
        'overall_points',
        'has_answers_shown',
    ];

    public function questions()
    {
        return $this->hasMany(Questions::class, 'activity_id');
    }
}
