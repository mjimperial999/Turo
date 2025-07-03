<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Students extends Model
{
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    protected $table = 'student'; // Name of The Table
    protected $primaryKey = 'user_id'; // Name of The Primary Key
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'isCatchUp',
        'total_points',
    ];

    public function progress()
    {
        return $this->hasMany(StudentProgress::class, 'student_id', 'user_id');
    }

    public function moduleProgresses()
    {
        return $this->hasMany(ModuleProgress::class,'student_id','user_id');
    }
}
