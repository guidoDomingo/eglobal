<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    public $timestamps      = false;
    protected $table        = 'etiquetas';
    protected $fillable     = ['tickets_campaigns_id', 'description', 'value'];
    
}
