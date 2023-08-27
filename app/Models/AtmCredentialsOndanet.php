<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtmCredentialsOndanet extends Model
{
    protected $table = 'ondanet_abm';

    protected $fillable = ['atm_id','vendedor','vendedor_cash','vendedor_descripcion','vendedor_descripcion_cash','caja','caja_cash','sucursal','sucursal_cash','deposito','deposito_cash','created_at','updated_at'];
}
