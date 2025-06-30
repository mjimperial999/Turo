<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutorials extends Model
{
    public function activity()
    {
        return $this->belongsTo(Activities::class, 'activity_id', 'activity_id');
    }

    protected $table = 'tutorial'; // Name of The Table
    protected $primaryKey = 'activity_id'; // Name of The Primary Key
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'activity_id',
        'content_type_id',
        'video_url'
    ];
}
