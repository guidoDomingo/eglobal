<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comision extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'mt_recibos_comisiones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['id', 'tipo_recibo_comision_id', 'monto', 'created_by', 'group_id', 'details', 'atm_id'];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function scopeName($query, $name)
    {   
        if (trim($name) != "") {                        
            $query->whereRaw('CAST(business_groups.description AS TEXT) LIKE '."'%$name%'");
        }
    }

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

    public static function filterAndPaginate($name)
    {

        return Comision::name($name)
            ->select([
                'mt_recibos_comisiones.*',
                'business_groups.description as cliente',
                'mt_tipo_recibo_comision.description'
            ])
            ->join('business_groups', 'business_groups.id', '=', 'mt_recibos_comisiones.group_id')
            ->join('mt_tipo_recibo_comision', 'mt_tipo_recibo_comision.id', '=', 'mt_recibos_comisiones.tipo_recibo_comision_id')
            ->orderBy('mt_recibos_comisiones.id', 'DESC')
        ->paginate(20);

    }
}
