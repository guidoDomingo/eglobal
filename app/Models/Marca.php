<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marca extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'marcas';

    /**
     * The attribute that are mass assignable
     * @var array
     */
    protected $fillable = ['categoria_id', 'descripcion', 'imagen_asociada', 'service_source_id'];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

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
    public function categorias()
    {
        return $this->hasOne('App\Models\AppCategory', 'id', 'categoria_id');
    }

    /**
     * Get Categories Model for marca
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service_sources()
    {
        return $this->hasOne('App\Models\ServiceProviderSource', 'id', 'service_source_id');
    }

    public function serviciosMarcas()
    {
        return $this->hasMany('App\Models\ServiciosMarca', 'marca_id');
    }

    public static function filterAndPaginate($name)
    {

        return Marca::name($name)
            ->with('categorias')
            ->with('service_sources')
            ->orderBy('id', 'DESC')
            ->paginate();
    }
}