<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'video_url',
        'pdf_blob',      
        'description'
    ];

    public function concept()
    {
        return $this->belongsTo(ScreeningConcept::class,
                                'screening_concept_id', 'screening_concept_id');
    }

    public function topic()
    {
        return $this->belongsTo(ScreeningTopic::class,
                                'screening_topic_id', 'screening_topic_id');
    }
}
