<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Presupuesto extends Model
{
    use SoftDeletes;

    protected $table     = 'grupos_presupuesto_reparacion';

    protected $fillable  = [
                            'group_id',
                            'numero',
                            'concepto',
                            'fecha',
                            'monto',
                            'comentario', 
                            'request_data',
                            'status_ondanet',
                            'num_venta',
                            'created_at',
                            'created_by',
                            'updated_at',
                            'deleted_at'
                        ];
  
}
