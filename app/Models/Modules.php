<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modules extends Model
{
    public function course() {
        return $this->belongsTo(Courses::class, 'course_id');
        // 'course_id' is the foreign key in the 'module' table
    }

    protected $table = 'module'; // Name of The Table
    protected $primaryKey = 'module_id'; // Name of The Primary Key
    public $timestamps = false;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'course_id',
        'module_name',
        'module_description',
        'module_image',
    ];

    public function activities()
    {
        return $this->hasMany(Activities::class, 'module_id');
        // 'module_id' is the foreign key in the 'activity' table
    }

    public function moduleimage()
    {
        return $this->hasOne(ModuleImage::class, 'module_id', 'module_id');
        // 'module_id' is the foreign key in the 'moduleimage' table
    }
}
