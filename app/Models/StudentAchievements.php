<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAchievements extends Model
{
    protected $table      = 'student_achievements';
    public    $timestamps = false;
    protected $fillable   = [
        'student_id',
        'achievement_id',
        'unlocked_at'
    ];

    public function achievement()
    {
        return $this->belongsTo(Achievements::class, 'achievement_id');
    }
    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id','user_id');
    }
}
