<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admin'; // Name of The Table
    protected $primaryKey = 'user_id'; // Name of The Primary Key
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_name',
        'password_hash',
    ];
}
