<?php
/*  app/Models/Achievements.php  */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievements extends Model
{
    protected $table      = 'achievements';
    protected $primaryKey = 'achievement_id';
    public    $incrementing = false;
    public    $timestamps  = false;
    protected $keyType     = 'string';

    protected $fillable = [
        'achievement_id', 'achievement_name', 'achievement_description',
        'achievement_image', 'condition_type_id', 'condition_value', 'is_unlocked'
    ];

    public function conditionType()
    {
        return $this->belongsTo(
            AchievementConditionType::class,
            'condition_type_id',
            'condition_type_id'
        );
    }

    public function unlockedBy()
    {
        return $this->hasMany(StudentAchievements::class, 'achievement_id');
    }
}
