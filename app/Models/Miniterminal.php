<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Miniterminal extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'housing';

    /**
     * The attribute that are mass assignable
     * @var array
     */
    protected $fillable = ['id', 'serialnumber', 'housing_type_id', 'installation_date'];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    /**
     * For Searches by name
     */
    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('observacion', 'ILIKE', "%$name%");
        }
    }

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    public static function filterAndPaginate($name)
    {
        return Miniterminal::name($name)
            ->orderBy('id', 'DESC')
            ->paginate();
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function UpdatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }
}