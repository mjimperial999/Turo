<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activities extends Model
{
    public function module()
    {
        return $this->belongsTo(Modules::class, 'module_id');
        // 'module_id' is the foreign key in the 'module' table
    }

    protected $table = 'activity'; // Name of The Table
    protected $primaryKey = 'activity_id'; // Name of The Primary Key
    public $timestamps = false;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'activity_id',
        'module_id',
        'activity_type',
        'activity_name',
        'activity_description',
        'unlock_date',
        'deadline_date',
    ];

    public function lecture()
    {
        return $this->hasOne(Lectures::class, 'activity_id', 'activity_id');
    }

    public function tutorial()
    {
        return $this->hasOne(Tutorials::class, 'activity_id', 'activity_id');
    }

    public function quiz()
    {
        return $this->hasOne(Quizzes::class, 'activity_id', 'activity_id');
    }

    public function results()
    {
    return $this->hasMany(AssessmentResult::class, 'activity_id', 'activity_id');
    }
}
