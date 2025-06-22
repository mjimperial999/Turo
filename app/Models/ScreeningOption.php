<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningOption extends Model
{
    protected $table      = 'screeningoption';
    protected $primaryKey = 'screening_option_id';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'screening_option_id', 'screening_question_id',
        'option_text', 'is_correct'
    ];

    public function question()
    {
        return $this->belongsTo(ScreeningQuestion::class,
                                'screening_question_id', 'screening_question_id');
    }
}
