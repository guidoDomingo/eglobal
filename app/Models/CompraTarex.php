<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompraTarex extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'compra_tarex';

    /**
     * The attribute that are mass assignable
     * @var array
     */

    protected $fillable = ['id','numero_factura', 'timbrado', 'forma_pago', 'producto', 'costo', 'desde', 'cantidad', 'status_ondanet', 'modalidad', 'created_by'];

    protected $dates = ['fecha', 'deleted_at', 'created_at', 'updated_at'];

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('forma_pago', 'ILIKE', "%$description%");
        }
    }

    public static function filterAndPaginate($description)
    {

        return CompraTarex::description($description)
            ->selectRaw('*, costo*cantidad as monto')
            ->orderBy('id', 'desc')
            ->paginate(20);

    }
}
