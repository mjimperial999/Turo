<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningResult extends Model
{
    protected $table      = 'screeningresult';
    protected $primaryKey = 'result_id';
    public    $incrementing = false;
    protected $keyType    = 'string';
    public $timestamps   = false; 

    protected $dates = ['date_taken'];

    protected $fillable = [
        'result_id',
        'screening_id',
        'student_id',
        'tier_id',
        'score_percentage',
        'earned_points',
        'attempt_number',
        'date_taken',
        'is_kept',
    ];

    public function screening()
    {
        return $this->belongsTo(Screening::class,
                                'screening_id', 'screening_id');
    }

    public function tier()
    {
        return $this->belongsTo(ScreeningTier::class,
                                'tier_id', 'tier_id');
    }

    public function answers()
    {
        return $this->hasMany(ScreeningResultAnswer::class,
                              'result_id', 'result_id');
    }
}
