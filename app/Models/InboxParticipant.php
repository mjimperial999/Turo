<?php
// app/Models/InboxParticipant.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InboxParticipant extends Model
{
    protected $table    = 'inboxparticipant';
    public    $incrementing = false;
    public    $timestamps   = false;
    protected $primaryKey = null;                     // composite PK

    protected $fillable = [
        'inbox_id',
        'participant_id'
    ];
}
