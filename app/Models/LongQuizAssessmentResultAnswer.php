<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LongQuizAssessmentResultAnswer extends Model
{
    /* ---------- table & primary key ---------- */
    protected $table      = 'long_assessmentresult_answer';
    protected $primaryKey = 'result_answer_id';
    protected $keyType = 'int';    
    public    $timestamps   = false;

    /* ---------- mass-assignable columns ---------- */
    protected $fillable = [
        'result_id',
        'long_quiz_question_id',
        'long_quiz_option_id',
        'is_correct',
    ];

    /* ---------- relationships ---------- */

    /** parent attempt */
    public function result()
    {
        return $this->belongsTo(
            LongQuizAssessmentResult::class,
            'result_id',
            'result_id'
        );
    }

    /** question meta */
    public function longquizquestion()
    {
        return $this->belongsTo(
            LongQuizQuestions::class,          // table: long_question
            'long_quiz_question_id',
            'long_quiz_question_id'
        );
    }

    /** selected option */
    public function longquizoption()
    {
        return $this->belongsTo(
            LongQuizOptions::class,            // table: long_option
            'long_quiz_option_id',
            'long_quiz_option_id'
        );
    }
}
