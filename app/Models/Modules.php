<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modules extends Model
{
    public function course() {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    protected $table = 'module'; 
    protected $primaryKey = 'module_id';
    public $timestamps = false;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'module_id',
        'course_id',
        'module_name',
        'module_description',
        'module_image',
    ];

    public function activities()
    {
        return $this->hasMany(Activities::class, 'module_id', 'module_id');
    }

    public function moduleimage()
    {
        return $this->hasOne(ModuleImage::class, 'module_id', 'module_id');
    }

    
}
