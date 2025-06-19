<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LongQuizQuestions extends Model
{
    public function longquiz()
    {
        return $this->belongsTo(Quizzes::class, 'long_quiz_id');
    }

    protected $table = 'longquiz_question'; // Name of The Table
    protected $primaryKey = 'long_quiz_question_id'; // Name of The Primary Key
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'question_text',
        'question_image',
        'question_type_id',
        'score',
    ];

    public function longquizoptions()
    {
        return $this->hasMany(LongQuizOptions::class, 'long_quiz_question_id');
    }

    public function longquizimage()
    {
        return $this->hasOne(LongQuizQuestionImages::class, 'long_quiz_question_id');
    }
}
