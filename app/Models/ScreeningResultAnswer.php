<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningResultAnswer extends Model
{
    protected $table      = 'screeningresult_answers';
    public    $incrementing = false;
    public    $timestamps   = false;

    protected $fillable = [
        'result_id',
        'screening_question_id',
        'screening_option_id',
        'is_correct',
    ];

    public function result()
    {
        return $this->belongsTo(ScreeningResult::class,
                                'result_id', 'result_id');
    }

    public function question()
    {
        return $this->belongsTo(ScreeningQuestion::class,
                                'screening_question_id', 'screening_question_id');
    }

    public function option()
    {
        return $this->belongsTo(ScreeningOption::class,
                                'screening_option_id', 'screening_option_id');
    }
}
