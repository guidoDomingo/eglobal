<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsuariosBahia extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'usuarios_bahia';

    /**
     * The attribute that are mass assignable
     * @var array
     */
    protected $fillable = ['nombre', 'ci', 'telefono', 'email'];

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
            $query->where('nombre', 'ILIKE', "%$name%");
        }
    }

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    public static function filterAndPaginate($name)
    {

        return UsuariosBahia::name($name)
            ->orderBy('id', 'DESC')
            ->paginate();
    }
}