<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Students extends Model
{
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    public function section()
    {
        return $this->belongsTo(Sections::class, 'section_id', 'section_id');
    }

    protected $table = 'student';
    protected $primaryKey = 'entry_id';
    public $timestamps = false;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'section',
        'isCatchUp',
        'total_points',
    ];

    public function progress()
    {
        return $this->hasMany(StudentProgress::class, 'student_id', 'user_id');
    }

    public function moduleProgresses()
    {
        return $this->hasMany(ModuleProgress::class, 'student_id', 'user_id');
    }

    public function scopeFilter($q, $term = null)
    {
        if (!$term) return;
        $like = "%$term%";
        $q->where(function ($x) use ($like) {
            $x->whereHas('user', fn($u) => $u->where(DB::raw("concat(last_name,' ',first_name)"), 'like', $like))
                ->orWhere('user_id', 'like', $like)
                ->orWhere('section_id', 'like', $like);
        });
    }
}
