<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiciosMarca extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'servicios_x_marca';

    /**
     * The attribute that are mass assignable
     * @var array
     */
    protected $fillable = ['marca_id', 'descripcion', 'imagen_asociada', 'service_source_id', 'ondanet_code', 'nivel', 'service_id', 'tipo', 'promedio_comision'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /*public function users()
    {
        return $this->hasMany('App\Models\User', 'owner_id');
    }*/
    /**
     * For Searches by name
     */
    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('descripcion', 'ILIKE', "%$name%");
            $query->orWhereHas('marcas', function($q) use($name){
                $q->where('marcas.descripcion', 'ILIKE', "%$name%");
            });
        }
    }

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Get Categories Model for marca
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function marcas()
    {
        return $this->hasOne('App\Models\Marca', 'id', 'marca_id');
    }

    public function serviceRules()
    {
        return $this->hasMany('App\Models\ServiceRule');
    }


    /**
     * Get Services Sources Model for marca
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service_sources()
    {
        return $this->hasOne('App\Models\ServiceProviderSource', 'id', 'service_source_id');
    }

    public static function filterAndPaginate($name)
    {
        return ServiciosMarca::name($name)
            ->with('marcas')
            ->with('service_sources')
            ->orderBy('created_at', 'DESC')
            ->withTrashed()
            ->where(function($query){
                $query->whereNull('deleted_at')
                    ->orWhereNotNull('deleted_at');
            })
            ->paginate();
    }
}