<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotaRetiro extends Model
{
    use SoftDeletes;

    protected $table     = 'grupos_nota_retiro';

    protected $fillable  = [//'atm_id',
                            'group_id',
                            'fecha',
                            'propietario',
                            'nombre_comercial', 
                            'direccion', 
                            'referencia',
                            'representante_legal',
                            'ruc_representante',
                            'created_at',
                            'created_by',
                            'updated_at',
                            'updated_by',
                            'deleted_at'];
  
}
