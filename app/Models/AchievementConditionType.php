<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AchievementConditionType extends Model
{
    protected $table      = 'achievementconditiontype';
    protected $primaryKey = 'condition_type_id';
    public    $incrementing = true;
    public    $timestamps  = false;

    /* one-to-many ← Achievements */
    public function achievements()
    {
        return $this->hasMany(Achievements::class, 'condition_type_id');
    }
}
