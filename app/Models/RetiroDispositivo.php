<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RetiroDispositivo extends Model
{
    use SoftDeletes;

    protected $table     = 'grupos_retiro_dispositivos';

    protected $fillable  = [
                            // 'atm_id',
                            'group_id',
                            'numero',
                            'fecha',
                            'encargado',
                            'firma',
                            'imagen',
                            'retirado',
                            'comentario',
                            'created_at',
                            'created_by',
                            'updated_at',
                            'updated_by',
                            'deleted_at',
                            'status_ondanet',
                            'numero_transferencia',
                            'request_data'
                            ];
  
}
