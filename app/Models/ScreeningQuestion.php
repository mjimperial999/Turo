<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningQuestion extends Model
{
    protected $table      = 'screeningquestion';
    protected $primaryKey = 'screening_question_id';
    public    $incrementing = false;
    protected $keyType    = 'string';
    public $timestamps   = false; 

    protected $fillable = [
        'screening_question_id',
        'screening_topic_id',
        'question_text',
        'question_type_id',
        'score',
    ];

    public function topic()
    {
        return $this->belongsTo(ScreeningTopic::class,
                                'screening_topic_id', 'screening_topic_id');
    }

    public function options()
    {
        return $this->hasMany(ScreeningOption::class,
                              'screening_question_id', 'screening_question_id');
    }

    public function image()
    {
        return $this->hasOne(ScreeningQuestionImage::class,
                             'screening_question_id', 'screening_question_id');
    }

    public function answers()
    {
        return $this->hasMany(ScreeningResultAnswer::class,
                              'screening_question_id', 'screening_question_id');
    }
}
