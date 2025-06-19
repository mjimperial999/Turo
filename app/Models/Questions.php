<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Questions extends Model
{
    public function quiz()
    {
        return $this->belongsTo(Quizzes::class, 'activity_id', 'activity_id');
    }

    protected $table = 'question'; // Name of The Table
    protected $primaryKey = 'question_id'; // Name of The Primary Key
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'question_type_id',
        'score',
        'activity',
    ];

    public function options()
    {
        return $this->hasMany(Options::class, 'question_id');
    }

    public function questionimage()
    {
        return $this->hasOne(QuestionImages::class, 'question_id');
    }
}
