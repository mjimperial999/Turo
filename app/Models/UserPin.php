<?php
// app/Models/UserPin.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPin extends Model
{
    protected $table      = 'user_pin';
    public    $timestamps = false;
    protected $primaryKey = 'user_id';
    public    $incrementing = false;

    protected $fillable = ['user_id', 'pin_code', 'expires_at'];

    /* helper: generates & stores a fresh pin (10-min TTL) */
    public static function issueFor($userId): string
    {
        $pin = random_int(100000, 999999);                // 6 digits
        static::updateOrCreate(
            ['user_id' => $userId],
            [
                'pin_code'   => $pin,
                'expires_at' => now('Asia/Manila')->addMinutes(10)
            ]
        );
        return (string)$pin;
    }
}
