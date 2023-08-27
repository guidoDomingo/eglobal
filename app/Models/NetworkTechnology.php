<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetworkTechnology extends Model
{
    public $timestamps = false;
 
    protected $table = 'network_technology';

    protected $fillable = ['id','description'];


}
