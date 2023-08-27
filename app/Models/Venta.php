<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'venta';

    /**
     * The attribute that are mass assignable
     * @var array
     */

    protected $fillable = ['id','housing_id','tipo_venta','destination_operation_id', 'response', 'amount', 'vendedor', 'group_id', 'acreedor_id', 'num_venta'];
    
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function housing()
    {
        return $this->belongsTo('App\Models\Housing', 'housing_id');
    }

    public function group()
    {
        return $this->belongsTo('App\Models\Group', 'group_id');
    }

    public function scopeNumVenta($query, $num_venta)
    {
        if (trim($num_venta) != "") {
            $query->where('num_venta', '=', "$num_venta");
        }
    }

    public static function filterAndPaginate($num_venta)
    {

        return Venta::numVenta($num_venta)
            ->select([
                'venta.*',
                'housing.serialnumber as num_serie'
            ])
            ->join('venta_housing', 'venta.id', '=', 'venta_housing.venta_id')
            ->join('housing', 'housing.id', '=', 'venta_housing.housing_id')
            ->with('group')
            ->orderBy('id', 'desc')
            ->paginate(20);

    }
}
