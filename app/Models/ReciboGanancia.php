<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReciboGanancia extends Model
{
    use SoftDeletes;

    protected $table     = 'grupos_recibo_ganancia';

    protected $fillable  = [
                            'group_id',
                            'numero', 
                            'fecha_finiquito',
                            'importe_cobrado',
                            'imagen', 
                            'gestionado',
                            'capital',
                            'interes',
                            'comentario',
                            'created_at',
                            'created_by',
                            'updated_at',
                            'updated_by',
                            'deleted_at'];
  
}
