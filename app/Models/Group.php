<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;
    
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'business_groups';

    /**
     * The attribute that are mass assignable
     * @var array
     */
    protected $fillable = ['id','description','ruc','created_by', 'updated_by', 'direccion', 'telefono'];
    
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }

    public function branches()
    {
        return $this->hasMany('App\Models\Branch');
    }

    public function ventas()
    {
        return $this->hasMany('App\Models\Venta');
    }

    public function alquiler()
    {
        return $this->hasMany('App\Models\Alquiler');
    }

    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('description', 'ILIKE', "%$description%");
        }
    }

    public static function filterAndPaginate($description)
    {

        return Group::description($description)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }
}
