<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImputacionDeuda extends Model
{
    use SoftDeletes;

    protected $table     = 'grupos_imputacion_deuda';

    protected $fillable  = [
                            'group_id',
                            'numero',
                            'numero_contrato',
                            'fecha_siniestro',
                            'fecha_cobro', 
                            'monto', 
                            'estado',
                            'procentaje_franquicia',
                            'created_at',
                            'created_by',
                            'updated_at',
                            'updated_by',
                            'deleted_at'
                        ];
  
}
