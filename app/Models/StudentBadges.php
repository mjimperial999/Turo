<?php
/*  app/Models/StudentBadge.php  */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentBadges extends Model
{
    protected $table      = 'student_badges';
    public    $timestamps = false;
    protected $fillable   = [
        'student_id',
        'badge_id',
        'unlocked_at'
    ];

    /* relationships */
    public function badge()
    {
        return $this->belongsTo(Badges::class, 'badge_id');
    }
    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id','user_id');
    }
}
