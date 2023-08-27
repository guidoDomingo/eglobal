<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepositoBoleta extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'boletas_depositos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['fecha', 'cuenta_bancaria_id', 'boleta_numero', 'monto','user_id', 'tipo_pago_id', 'updated_by', 'atm_id', 'imagen_asociada', 'monto_anterior'];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function UpdatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }

    /*public function banco()
    {
        return $this->hasOne('App\Models\Banco', 'id', 'banco_id');
    }*/

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
            $query->where('boleta_numero', 'ILIKE', "%$name%");
        }
    }

    public static function filterAndPaginate($name)
    {
        
        if(\Sentinel::getUser()->hasAccess('depositos_boletas') && !\Sentinel::getUser()->hasAccess('superuser') && !\Sentinel::getUser()->hasRole('mantenimiento.operativo') && !\Sentinel::getUser()->hasRole('accounting.admin')){

            $begin= Carbon::today();
            $end= Carbon::tomorrow();

            if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
    
                return DepositoBoleta::name($name)
                ->select([
                    'boletas_depositos.*',
                    'atms.name'
                ])
                ->join('atms', 'atms.id', '=', 'boletas_depositos.atm_id')
                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('users_x_groups', 'branches.group_id', '=', 'users_x_groups.group_id')
                ->where('users_x_groups.user_id', \Sentinel::getUser()->id)
                ->where('estado', null)
                ->whereBetween('boletas_depositos.created_at',[$begin,$end])
                ->orderBy('boletas_depositos.id', 'desc')
                ->paginate(20);
    
            }else{
                return DepositoBoleta::name($name)
                ->select([
                    'boletas_depositos.*',
                    'atms.name'
                ])
                ->join('atms', 'atms.id', '=', 'boletas_depositos.atm_id')
                ->where('user_id', \Sentinel::getUser()->id)
                ->where('estado', null)
                ->whereBetween('boletas_depositos.created_at',[$begin,$end])
                ->orderBy('id', 'desc')
                ->paginate(20);
            }

        }else{
            return DepositoBoleta::name($name)
            ->select([
                'boletas_depositos.*',
                'atms.name'
            ])
            ->join('atms', 'atms.id', '=', 'boletas_depositos.atm_id')
            ->where('estado', null)
            ->orderBy('id', 'desc')
            ->paginate(20);
        }
    }
}
