<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LongQuizQuestionImages extends Model
{
    public function longquizquestion() {
        return $this->belongsTo(LongQuizQuestions::class, 'long_quiz_question_id');
    }

    protected $table = 'longquiz_question_image'; // Name of The Table
    protected $primaryKey = 'lq_image_id'; // Name of The Primary Key
    public $timestamps = false;

    protected $fillable = [
        'long_quiz_question_id',
        'image',
    ];
}
