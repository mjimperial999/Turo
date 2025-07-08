<?php
// app/Models/AssessmentAnswer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentResultAnswer extends Model
{
    /* ---------- table & primary-key ---------- */
    protected $table      = 'assessmentresult_answers';   // exact table name
    protected $primaryKey = null;                         // composite PK → handled manually
    public    $incrementing = false;
    public    $timestamps   = false;                      // no created_at / updated_at columns

    /* ---------- mass-assignable columns ---------- */
    protected $fillable = [
        'result_id',
        'question_id',
        'option_id',
        'is_correct',
    ];

    /* ---------- relationships ---------- */

    /** parent “attempt” row */
    public function result()
    {
        return $this->belongsTo(
            AssessmentResult::class,
            'result_id',
            'result_id'
        );
    }

    /** quiz question (for text, type id, etc.) */
    public function question()
    {
        return $this->belongsTo(
            Questions::class,
            'question_id',
            'question_id'
        );
    }

    /** option chosen by the student */
    public function option()
    {
        return $this->belongsTo(
            Options::class,
            'option_id',
            'option_id'
        );
    }
}
