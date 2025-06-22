<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Screening extends Model {           // maps table `screening`
    protected $primaryKey = 'screening_id';
    public $incrementing = false;
    public function concepts() { return $this->hasMany(ScreeningConcept::class); }
    public function results()  { return $this->hasMany(ScreeningResult::class); }
}

class ScreeningConcept extends Model {    // table `screeningconcept`
    protected $primaryKey = 'screening_concept_id';
    public $incrementing = false;
    public function screening() { return $this->belongsTo(Screening::class); }
    public function topics()    { return $this->hasMany(ScreeningTopic::class); }
}

class ScreeningTopic extends Model {      // table `screeningtopic`
    protected $primaryKey = 'screening_topic_id';
    public $incrementing = false;
    public function concept()   { return $this->belongsTo(ScreeningConcept::class); }
    public function questions() { return $this->hasMany(ScreeningQuestion::class); }
}

class ScreeningQuestion extends Model {   // table `screeningquestion`
    protected $primaryKey = 'screening_question_id';
    public $incrementing = false;
    public function topic()   { return $this->belongsTo(ScreeningTopic::class); }
    public function options() { return $this->hasMany(ScreeningOption::class); }
}

class ScreeningOption extends Model {     // table `screeningoption`
    protected $primaryKey = 'screening_option_id';
    public $incrementing = false;
    public function question() { return $this->belongsTo(ScreeningQuestion::class); }
}

class ScreeningResult extends Model {     // table `screeningresult`
    protected $primaryKey = 'result_id';
    public $incrementing = false;
    public function screening() { return $this->belongsTo(Screening::class); }
    public function answers()  { return $this->hasMany(ScreeningResultAnswer::class,'result_id'); }
}

class ScreeningResultAnswer extends Model { // table `screeningresult_answers`
    public $incrementing = false;
    protected $primaryKey = null;
    public $timestamps = false;
}

class ScreeningTier extends Model {       // table `screeningtier`
    protected $primaryKey = 'tier_id';
    public $incrementing = false;
    public $timestamps   = false;
}

class LearningResource extends Model
{
    protected $table      = 'learningresource';
    protected $primaryKey = 'learning_resource_id';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'learning_resource_id',
        'screening_concept_id',
        'screening_topic_id',
        'title',
        'resource_type',
        'resource_url',
        'description',
        'slug'
    ];

    /* ---------- relationships ---------- */
    public function concept()
    {
        return $this->belongsTo(ScreeningConcept::class, 'screening_concept_id');
    }

    public function topic()
    {
        return $this->belongsTo(ScreeningTopic::class, 'screening_topic_id');
    }
}
