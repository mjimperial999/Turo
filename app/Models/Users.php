<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Users extends Authenticatable    
{
    use HasApiTokens, Notifiable; 

    protected $table = 'user';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'password_hash',
        'role_id',
        'agreed_to_terms',
        'requires_password_change',
    ];

    public function student()
    {
        return $this->hasMany(Students::class, 'user_id');
    }

    public function teacher()
    {
        return $this->hasMany(Teachers::class, 'user_id');
    }

    public function image()
    {
        return $this->hasOne(UserImages::class, 'user_id');
    }
}