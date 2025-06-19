<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionImages extends Model
{
    public function question() {
        return $this->belongsTo(Questions::class, 'question_id');
    }

    protected $table = 'quiz_question_image'; // Name of The Table
    protected $primaryKey = 'q_image_id'; // Name of The Primary Key
    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'image',
    ];
}
