<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LongQuizOptions extends Model
{
       public function longquizquestion()
    {
        return $this->belongsTo(LongQuizQuestions::class, 'long_quiz_question_id');
    }

    protected $table = 'longquiz_option'; // Name of The Table
    protected $primaryKey = 'long_quiz_option_id'; // Name of The Primary Key
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'long_quiz_option_id',
        'long_quiz_question_id',
        'option_text',
        'is_correct',
    ];
}
