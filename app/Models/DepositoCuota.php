<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepositoCuota extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'recibo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['id','movements_id','fecha', 'cuenta_bancaria_id', 'boleta_numero', 'monto','user_id', 'tipo_pago_id', 'updated_by', 'estado','user_id','updated_by'];

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
            $query->where('boleta_numero', 'ILIKE', "%$name%");
        }
    }

    public static function filterAndPaginate($name)
    {
        
        if(\Sentinel::getUser()->hasAccess('depositos_cuotas') && !\Sentinel::getUser()->hasAccess('superuser') && !\Sentinel::getUser()->hasRole('mantenimiento.operativo') && !\Sentinel::getUser()->hasRole('accounting.admin')){

            $begin= Carbon::today();
            $end= Carbon::tomorrow();

            return DepositoCuota::name($name)
            ->where('user_id', \Sentinel::getUser()->id)
            ->where('estado', null)
            ->whereBetween('created_at',[$begin,$end])
            ->orderBy('id', 'desc')
            ->paginate(20);

        }else{
            return DepositoCuota::name($name)
            ->where('estado', null)
            ->orderBy('id', 'desc')
            ->paginate(20);
        }
    }
}
