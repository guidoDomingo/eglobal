<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CanalCliente extends Model
{
    use SoftDeletes;
   
    protected $table        = 'canal';
    protected $fillable     = ['descripcion'];
    protected $dates        = ['deleted_at', 'created_at', 'updated_at'];

    
    
}
