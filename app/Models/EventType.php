<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    /* ───── table / key ───────────────────────────── */
    protected $table      = 'eventtype';        // ← exact table name in dump
    protected $primaryKey = 'event_type_id';    // ← PK column
    public    $incrementing = false;            // small INT but we’ll set it
    public    $timestamps   = false;            // table has no created_at / updated_at
    protected $keyType    = 'int';              // INT PK

    /* ───── mass-assignable ───────────────────────── */
    protected $fillable = [
        'event_type_id',      // 1, 2, 3 … (from the SQL dump)
        'event_type_name',    // “ANNOUNCEMENT”, “LONG_QUIZ”, …
    ];

    /* ───── relations ─────────────────────────────── */
    /** Every calendar event that uses this type */
    public function events()
    {
        return $this->hasMany(
            CalendarEvent::class,
            'event_type_id',       // FK on calendarevent
            'event_type_id'        // this model’s key
        );
    }
}
