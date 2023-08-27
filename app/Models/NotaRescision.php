<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotaRescision extends Model
{
    use SoftDeletes;

    protected $table     = 'grupos_nota_rescision';
    protected $fillable  = ['group_id',
                            'numero',
                            'nombre_comercial', 
                            'fecha', 
                            'direccion', 
                            'created_at',
                            'created_by',
                            'updated_at',
                            'updated_by',
                            'deleted_at'];
  
}
