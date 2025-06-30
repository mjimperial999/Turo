<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Options extends Model
{
    public function question()
    {
        return $this->belongsTo(Questions::class, 'question_id', 'question_id');
    }

    protected $table = 'options'; // Name of The Table
    protected $primaryKey = 'option_id'; // Name of The Primary Key
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'option_id',
        'question_id',
        'option_text',
        'is_correct',
    ];
}
