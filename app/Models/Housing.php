<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Housing extends Model
{
    public $timestamps = false;//PARA DESACTIVAR LA MARCA DE TIEMPO UPDATED_AT Y CREATED_aT
    /**
     * The database table used by the model
     * @var string
     */

    protected $table = 'housing';

    /**
     * The attribute that are mass assignable
     * @var array
     */
    protected $fillable = ['id','serialnumber','housing_type_id','installation_date'];
    


    public function scopeName($query, $name)
    {   
        if (trim($name) != "") {
            $query->where('serialnumber', 'ILIKE', "%$name%");
        }

    }

    public static function filterAndPaginate($name)
    {
        return Housing::name($name)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }
}
