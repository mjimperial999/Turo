<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CalendarEvent extends Model
{
    /* ===== table / pk ===== */
    protected $table      = 'calendarevent';   // dump shows this exact name
    protected $primaryKey = 'event_id';
    public    $incrementing = false;           // varchar IDs
    public    $timestamps   = false;           // table has no created_at / updated_at

    protected $keyType = 'string';

    /* ===== mass-assignable ===== */
    protected $fillable = [
        'event_id',
        'title',
        'description',
        'date',
        'event_type_id',
        'is_urgent',
        'location',
    ];

    /* ===== casts ===== */
    protected $casts = [
        'date'      => 'datetime',
        'is_urgent' => 'boolean',
    ];

    /* ===== RELATIONS (optional) ===== */
    public function type()        // ⇢ eventtype table
    {
        return $this->belongsTo(EventType::class, 'event_type_id', 'event_type_id');
    }

    /* convenience: true if the event is still in the future */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->date->isFuture();
    }

    /* nice formatted date for notifications */
    public function getNiceDateAttribute(): string
    {
        return $this->date->format('M j, Y g:i A');
    }
}
