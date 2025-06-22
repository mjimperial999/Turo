<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Screening extends Model
{
    protected $table      = 'screening';
    protected $primaryKey = 'screening_id';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'screening_id', 'course_id', 'screening_name',
        'screening_instructions', 'time_limit', 'number_of_questions'
    ];

    /* FK = screening_id in child, PK = screening_id here */
    public function concepts()
    {
        return $this->hasMany(ScreeningConcept::class,
                              'screening_id',        // child FK
                              'screening_id');       // local PK
    }

    public function image()
    {
        return $this->hasOne(ScreeningImage::class,
                             'screening_id', 'screening_id');
    }

    public function results()
    {
        return $this->hasMany(ScreeningResult::class,
                              'screening_id', 'screening_id');
    }
}
