<?php
/*  app/Models/Badges.php  */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badges extends Model
{
    protected $table      = 'badges';
    protected $primaryKey = 'badge_id';
    public    $incrementing = false;
    public    $timestamps  = false;
    protected $keyType     = 'string';

    protected $fillable = [
        'badge_id','badge_name','badge_description',
        'badge_image','points_required'
    ];

    public function unlockedBy()
    {
        return $this->hasMany(StudentBadges::class, 'badge_id');
    }
}
