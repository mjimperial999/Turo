<?php
// app/Models/Inbox.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Inbox extends Model
{
    protected $table      = 'inbox';
    protected $primaryKey = 'inbox_id';
    public    $incrementing = false;
    protected $keyType    = 'string';
    public    $timestamps = false;

    protected $fillable   = [
        'inbox_id',
        'unread_count',
        'timestamp'
    ];

    public function participants()
    {
        return $this->belongsToMany(
            Users::class,
            'inboxparticipant',
            'inbox_id',
            'participant_id',
            'inbox_id',
            'user_id'
        );
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'inbox_id', 'inbox_id')
            ->orderBy('timestamp');
    }
}
