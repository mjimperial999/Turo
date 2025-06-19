<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = 'user'; // Name of The Table
    protected $primaryKey = 'user_id'; // Name of The Primary Key
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password_hash',
        'role_id',
        'profile_pic',
        'agreed_to_terms',
        'requires_password_change',
    ];

    public function student()
    {
        return $this->hasMany(Students::class, 'user_id');
    }

    public function image()
    {
        return $this->hasOne(UserImages::class, 'user_id');
    }
}