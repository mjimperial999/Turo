<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Screening extends Model
{
    protected $table      = 'screening';
    protected $primaryKey = 'screening_id';
    public    $incrementing = false;
    protected $keyType    = 'string';
    public $timestamps   = false; 

    protected $fillable = [
        'screening_id',
        'course_id',
        'screening_name',
        'screening_instructions',
        'time_limit',
        'number_of_questions'
    ];

    public function concepts()
    {
        return $this->hasMany(ScreeningConcept::class, 'screening_id', 'screening_id');
    }

    public function image()
    {
        return $this->hasOne(ScreeningImage::class, 'screening_id', 'screening_id');
    }

    public function results()
    {
        return $this->hasMany(ScreeningResult::class, 'screening_id', 'screening_id');
    }

    public function keptResult($userID = null)
    {
        return $this->hasOne(
            ScreeningResult::class,
            'screening_id',
            'screening_id'
        )
            ->where('is_kept', 1)
            ->when($userID, fn($q) => $q->where('student_id', $userID));
    }
}
