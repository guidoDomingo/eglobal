<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RemisionPagare extends Model
{
    use SoftDeletes;

    protected $table     = 'grupos_pagares_remision';
    protected $fillable  = [
                            'group_id', 
                            'numero',
                            'titular_deudor', 
                            'fecha', 
                            'importe',
                            'nro_contrato',
                            'importe_deuda',
                            'importe_imputado',
                            'recepcionado',
                            'created_at',
                            'created_by',
                            'updated_at',
                            'updated_by',
                            'deleted_at'
                            ];
  
}
