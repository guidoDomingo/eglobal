<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ciudad extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'ciudades';

    /**
     * The attribute that are mass assignable
     * @var array
     */
    protected $fillable = ['descripcion','departamento_id'];

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
            $query->where(function($sql) use($name){
                $sql->where('descripcion', 'ILIKE', "%$name%");
                $sql->orWhere('descripcion', 'ILIKE', "%$name%");
                $sql->orWhereHas('departamentos', function($q) use($name){
                    $q->where('descripcion', 'ILIKE', "%$name%");
                });
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
    public function departamentos()
    {
        return $this->hasOne('App\Models\Departamento', 'id', 'departamento_id');
    }

    /**
     * Get Categories Model for marca
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    /*public function service_sources()
    {
        return $this->hasOne('App\Models\ServiceProviderSource', 'id', 'service_source_id');
    }

    public function serviciosMarcas()
    {
        return $this->hasMany('App\Models\ServiciosMarca', 'marca_id');
    }*/

    public static function filterAndPaginate($name)
    {

        return Ciudad::name($name)
            ->with('departamentos')
            ->orderBy('id', 'DESC')
            ->paginate();
    }
}