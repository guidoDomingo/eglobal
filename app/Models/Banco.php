<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banco extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'bancos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['id','descripcion'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Filter by description
     */
    public function scopeDescripcion($query, $descripcion)
    {
        if (trim($descripcion) != "") {
            $query->where('descripcion', 'ILIKE', "%$descripcion%");
        }
    }

    public static function filterAndPaginate($name)
    {
        return Banco::descripcion($name)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }

}
