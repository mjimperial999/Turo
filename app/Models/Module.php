<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    // Tell Eloquent which table already exists
    protected $table = 'modules';        // adjust if the table is named differently
    protected $primaryKey = 'module_id'; // or 'id', etc.

    // If your table has no created_at / updated_at columns
    public $timestamps = false;

    // (optional) fillable columns for mass-assignment
    protected $fillable = [
        'module_name',
        'module_description',
        // add the columns you need
    ];
}
