<?php
// app/Models/MessageUserState.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageUserState extends Model
{
    protected $table    = 'messageuserstate';
    public    $incrementing = false;
    public    $timestamps   = false;
    protected $primaryKey = null;                     // composite PK

    protected $fillable = [
        'message_id',
        'user_id',
        'is_read',
        'is_deleted'
    ];

    /* ---- relations ---- */
    public function message()
    {   return $this->belongsTo(Message::class); }

    public function user()
    {   return $this->belongsTo(Users::class, 'user_id', 'user_id'); }
}
