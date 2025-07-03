<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleProgress extends Model
{
    public function student()
    {
        return $this->belongsTo(Students::class,'student_id','user_id');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class,'course_id','course_id');
    }

    public function module()
    {
        return $this->belongsTo(Modules::class,'module_id','module_id');
    }

    protected $table = 'moduleprogress';
    protected $primaryKey = 'module_id';
    public $incrementing = false;
    public $timestamps  = false;

    protected $keyType = 'string';


    protected $fillable = [
        'student_id',
        'course_id',
        'module_id',
        'progress',
        'is_completed',
        'average_score',
    ];

    public function scopeCompleted($q)
    {
        return $q->where('is_completed', 1);
    }
}
