<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelBrand extends Model
{
   
    protected $table = 'model';
    public $timestamps = false;//PARA DESACTIVAR LA MARCA DE TIEMPO UPDATED_AT Y CREATED_aT
    protected $fillable = ['id','brand_id', 'description','created_at','priority'];

    public function brand()
    {
        return $this->belongsTo('App\Models\Brand');

    }

    public function scopeBrand($query, $brandId)
    {
        if(!is_null($brandId)){
            $query->where('brand_id', $brandId);
            //dd($query->toSql());
        }
         //dd($query->toSql());
    }
    
    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('description', 'ILIKE', "%$description%");
           //dd($query->toSql());
        }
    }
    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('description', 'ILIKE', "%$name%");
           //dd($query->toSql());
        }
    }

    public static function filterAndPaginate($brandId, $name)
    {
        //dd($housingId);
        return ModelBrand::description($name)
            ->brand($brandId)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }

   


}
