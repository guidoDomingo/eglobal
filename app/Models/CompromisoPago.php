<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompromisoPago extends Model
{
    use SoftDeletes;

    protected $table     = 'grupos_compromiso_pago';

    protected $fillable  = [
                            'group_id',
                            'numero', 
                            'fecha',
                            'monto',
                            'cantidad_pago', 
                            'estado',
                            'comentario',
                            'created_at',
                            'created_by',
                            'updated_at',
                            'updated_by',
                            'deleted_at'];
  
}
