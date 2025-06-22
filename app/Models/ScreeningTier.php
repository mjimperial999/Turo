<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningTier extends Model
{
    protected $table      = 'screeningtier';      // :contentReference[oaicite:20]{index=20}
    protected $primaryKey = 'tier_id';
    public    $incrementing = true;
    protected $keyType    = 'int';

    public $timestamps = false;

    protected $fillable = ['tier_id', 'tier_name'];

    public function results() { return $this->hasMany(ScreeningResult::class, 'tier_id'); }
}
