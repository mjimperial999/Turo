<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lectures extends Model
{
    public function activity()
    {
        return $this->belongsTo(Activities::class, 'activity_id', 'activity_id');
    }

    protected $table = 'lecture'; // Name of The Table
    protected $primaryKey = 'activity_id'; // Name of The Primary Key
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'content_type_id',
        'activity_id',
        'text_body',
        'file_name',
    ];
}
