<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserImages extends Model
{
    public function user() {
        return $this->belongsTo(Users::class, 'user_id');
    }

    protected $table = 'user_image'; // Name of The Table
    protected $primaryKey = 'user_image_id'; // Name of The Primary Key
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'image',
    ];
}
