<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningQuestionImage extends Model
{
    protected $table      = 'screeningquestion_image';
    protected $primaryKey = 'screening_question_image_id';
    public    $incrementing = true;

    protected $fillable = [
        'screening_question_image_id', 'screening_question_id', 'image'
    ];

    public function question()
    {
        return $this->belongsTo(ScreeningQuestion::class,
                                'screening_question_id', 'screening_question_id');
    }
}
