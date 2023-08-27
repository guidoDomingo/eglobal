<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Isp extends Model
{
    public $timestamps = false;
   
    protected $table = 'isp';

    protected $fillable = ['id','description'];
    
}
