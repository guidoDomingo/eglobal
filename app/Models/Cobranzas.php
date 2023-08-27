<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cobranzas extends Model
{
    use SoftDeletes;

    protected $table     = 'grupos_cobranzas';
    protected $fillable  = [//'atm_id',
                            'group_id', 
                            'numero',
                            'firmante', 
                            'tipo', 
                            'vencimiento',
                            'monto',
                            'tasa_interes',
                            'cantidad_pagos',
                            'created_at',
                            'created_by',
                            'updated_at',
                            'updated_by',
                            'deleted_at'
                            //'deleted_by'
                        ];
  
}
