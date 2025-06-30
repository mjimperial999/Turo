<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningConcept extends Model
{
    protected $table      = 'screeningconcept';
    protected $primaryKey = 'screening_concept_id';
    public    $incrementing = false;
    protected $keyType    = 'string';
    public $timestamps   = false; 

    protected $fillable = [
        'screening_concept_id', 
        'screening_id',
        'concept_name',
        'passing_score',
    ];

    public function screening()
    {
        return $this->belongsTo(Screening::class,
                                'screening_id', 'screening_id');
    }

    public function topics()
    {
        return $this->hasMany(ScreeningTopic::class,
                              'screening_concept_id', 'screening_concept_id');
    }

    public function resources()
    {
        return $this->hasMany(LearningResource::class,
                              'screening_concept_id', 'screening_concept_id');
    }
}
