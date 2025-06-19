<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleImage extends Model
{
    public function module() {
        return $this->belongsTo(Courses::class, 'module_id');
    }

    protected $table = 'module_image'; // Name of The Table
    protected $primaryKey = 'image_id'; // Name of The Primary Key
    public $timestamps = false;

    protected $fillable = [
        'module_id',
        'image',
    ];
}
