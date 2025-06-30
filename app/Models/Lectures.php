<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lectures extends Model
{
    public function activity()
    {
        return $this->belongsTo(Activities::class, 'activity_id', 'activity_id');
    }

    protected $table = 'lecture';
    protected $primaryKey = 'activity_id';
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'content_type_id',
        'activity_id',
        'file_url',
        'file_mime_type',
        'file_name',
    ];
}
