<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningTopic extends Model
{
    protected $table      = 'screeningtopic';
    protected $primaryKey = 'screening_topic_id';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'screening_topic_id', 'screening_concept_id', 'topic_name'
    ];

    public function concept()
    {
        return $this->belongsTo(ScreeningConcept::class,
                                'screening_concept_id', 'screening_concept_id');
    }

    public function questions()
    {
        return $this->hasMany(ScreeningQuestion::class,
                              'screening_topic_id', 'screening_topic_id');
    }

    public function resources()
    {
        return $this->hasMany(LearningResource::class,
                              'screening_topic_id', 'screening_topic_id');
    }
}
