<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PagoCliente extends Model
{
    use SoftDeletes;
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'mt_pago_clientes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['monto', 'group_id', 'atm_id', 'txt_generated','estado', 'created_by', 'updated_by'];

    protected $dates = ['fecha_proceso','deleted_at', 'created_at', 'updated_at'];

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

    public function UpdatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }
    
    public function group()
    {
        return $this->hasOne('App\Models\Group', 'id', 'group_id');
    }

    public function atm()
    {
        return $this->hasOne('App\Models\Atm', 'atm_id');
    }

    public function scopeName($query, $name)
    {   
        if (trim($name) != "") {
            $query->where('group', 'ILIKE', "%$name%");
        }
    }

    public static function filterAndPaginate($name)
    {
        return PagoCLiente::name($name)
            ->where('estado', null)
            ->orderBy('id', 'desc')
        ->paginate(20);
    }
}
