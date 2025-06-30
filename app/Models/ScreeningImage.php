<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningImage extends Model
{
    protected $table      = 'screening_image';
    protected $primaryKey = 'screening_img_id';
    public    $incrementing = true;
    public $timestamps   = false; 

    protected $fillable = [
        'screening_img_id',
        'screening_id',
        'image'
    ];

    public function screening()
    {
        return $this->belongsTo(Screening::class,
                                'screening_id', 'screening_id');
    }
}
