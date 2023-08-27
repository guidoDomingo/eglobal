<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mt_recibos_pagos_miniterminales extends Model
{    
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'mt_recibos_pagos_miniterminales';    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'boleta_numero',
        'concepto',
        'cuenta_bancaria_id',
        'estado',
        'fecha',
        'id',
        'message',
        'recibo_id',
        'tipo_pago_id',
        'updated_by',
        'user_id',
        'monto',
        'tipo_recibo_id',
        'atm_id'

    ];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function UpdatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }

    public function cuentaBancaria()
    {
        return $this->hasOne('App\Models\CuentaBancaria', 'id', 'cuenta_bancaria_id');
    }

    public function tipoPago()
    {
        return $this->hasOne('App\Models\TipoPago', 'id', 'tipo_pago_id');
    }

    public function scopeName($query, $name)
    {   
        if (trim($name) != "") {                        
            $query->whereRaw('CAST(boleta_numero AS TEXT) LIKE '."'%$name%'");
        }
    }

    public static function filterAndPaginate($name)
    {
        
        if(\Sentinel::getUser()->hasAccess('depositos_cuotas') && !\Sentinel::getUser()->hasAccess('superuser') && !\Sentinel::getUser()->hasRole('mantenimiento.operativo') && !\Sentinel::getUser()->hasRole('accounting.admin')){

            $begin  = Carbon::today();
            $end    = Carbon::tomorrow();

            if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){       
                return mt_recibos_pagos_miniterminales::name($name)
                ->select([
                    'mt_recibos_pagos_miniterminales.*',
                    'atms.name'
                ])
                ->join('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('users_x_groups', 'branches.group_id', '=', 'users_x_groups.group_id')
                ->where('users_x_groups.user_id', \Sentinel::getUser()->id)
                ->where('estado', null)
                //->where('tipo_recibo_id',1)
                ->whereBetween('mt_recibos_pagos_miniterminales.created_at',[$begin,$end])
                ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                ->paginate(20);
            }else{
                return mt_recibos_pagos_miniterminales::name($name)
                ->select([
                    'mt_recibos_pagos_miniterminales.*',
                    'atms.name'
                ])
                ->join('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
                ->where('user_id', \Sentinel::getUser()->id)
                ->where('estado', null)
                //->where('tipo_recibo_id',1)
                ->whereBetween('mt_recibos_pagos_miniterminales.created_at',[$begin,$end])
                ->orderBy('id', 'desc')
                ->paginate(20);
            }

        }else{
            return mt_recibos_pagos_miniterminales::name($name)
            ->select([
                'mt_recibos_pagos_miniterminales.*',
                'atms.name'
            ])
            ->join('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
            ->where('estado', null)
            //->where('tipo_recibo_id',1)
            ->orderBy('id', 'desc')
            ->paginate(20);
        }
    }
}
