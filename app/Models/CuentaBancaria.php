<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuentaBancaria extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cuentas_bancarias';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['numero_banco','banco_id'];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function banco()
    {
        return $this->hasOne('App\Models\Banco', 'id', 'banco_id');
    }


    /**
     * Filter by description
     */
    public function scopeNumeroBanco($query, $numero_banco)
    {
        if (trim($numero_banco) != "") {
            $query->where('numero_banco', 'ILIKE', "%$numero_banco%");
        }
    }

    public static function filterAndPaginate($numero_banco)
    {
        return CuentaBancaria::numero_banco($numero_banco)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }
}
