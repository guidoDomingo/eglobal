<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zona extends Model
{
    /**
     * The database table used by the model
     * @var string
     */
    public $timestamps = false;

    protected $table = 'zona';

    /**
     * The attribute that are mass assignable
     * @var array
     */
    protected $fillable = ['descripcion','users_id'];


    /*public function users()
    {
        return $this->hasMany('App\Models\User', 'owner_id');
    }*/
  
    // public function scopeName($query, $name)
    // {
    //     if (trim($name) != "") {
    //         $query->where(function($sql) use($name){
    //             $sql->where('descripcion', 'ILIKE', "%$name%");
    //             $sql->orWhere('descripcion', 'ILIKE', "%$name%");
    //             $sql->orWhereHas('departamentos', function($q) use($name){
    //                 $q->where('descripcion', 'ILIKE', "%$name%");
    //             });
    //         });
    //     }
    // }


}