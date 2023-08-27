<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Intimacion extends Model
{
    use SoftDeletes;

    protected $table     = 'grupos_intimacion';
    protected $fillable  = ['group_id',
                             'numero', 
                             'fecha_envio', 
                             'fecha_vencimiento',
                             'fecha_recepcion',
                             'created_at',
                             'created_by',
                             'updated_at',
                             'updated_by',
                             'deleted_at'];
  
}
