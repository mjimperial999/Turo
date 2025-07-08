<?php
// app/Models/Message.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table      = 'message';
    protected $primaryKey = 'message_id';
    public    $incrementing = false;
    protected $keyType    = 'string';
    public    $timestamps = false;

    protected $fillable   = [
        'message_id',
        'inbox_id',
        'sender_id',
        'subject',
        'body',
        'timestamp'
    ];

    /* ---- relations ---- */
    public function inbox()
    {
        return $this->belongsTo(Inbox::class);
    }

    public function sender()
    {
        return $this->belongsTo(Users::class, 'sender_id', 'user_id');
    }

    public function userStates()
    {
        return $this->hasMany(MessageUserState::class, 'message_id', 'message_id');
    }
}
