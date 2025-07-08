<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningResultAnswer extends Model
{
    /* ---------- table & PK ---------- */
    protected $table      = 'screeningresult_answers';
    protected $primaryKey = null;             // composite PK → result_id + screening_question_id
    public    $incrementing = false;
    public    $timestamps   = false;

    /* ---------- mass-assignable fields ---------- */
    protected $fillable = [
        'result_id',
        'screening_question_id',
        'screening_option_id',
        'is_correct',
    ];

    /* ---------- relationships ---------- */

    /** parent screening-result row */
    public function result()
    {
        return $this->belongsTo(
            ScreeningResult::class,          // ← create if not yet present
            'result_id',
            'result_id'
        );
    }

    /** question meta (text, concept/topic, etc.) */
    public function question()
    {
        return $this->belongsTo(
            ScreeningQuestion::class,        // table: screeningquestion
            'screening_question_id',
            'screening_question_id'
        );
    }

    /** option chosen by the student */
    public function option()
    {
        return $this->belongsTo(
            ScreeningOption::class,          // table: screeningoption
            'screening_option_id',
            'screening_option_id'
        );
    }
}
