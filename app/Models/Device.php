<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
   
    protected $table = 'device';
    public $timestamps = false;//PARA DESACTIVAR LA MARCA DE TIEMPO UPDATED_AT Y CREATED_aT
    protected $fillable = ['id','serialnumber', 'descripcion','installation_date', 'housing_id', 'activo' ,'activated_at', 'model_id'];

    public function housing()
    {
        return $this->belongsTo('App\Models\Housing');

    }

    public function model()
    {
        return $this->belongsTo('App\Models\ModelBrand');

    }

    public function scopeHousing($query, $housingId)
    {
        //dd($housingId);
        if(!is_null($housingId)){
            $query->where('housing_id', $housingId);
            //dd($query->toSql());
        }
         //dd($query->toSql());
    }
    
    public function scopeDescription($query, $serialnumber)
    {
        if (trim($serialnumber) != "") {
            $query->where('serialnumber', 'ILIKE', "%$serialnumber%");
           //dd($query->toSql());
        }
    }
    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('serialnumber', 'ILIKE', "%$name%");
           //dd($query->toSql());
        }
    }

    public static function filterAndPaginate($housingId, $name)
    {
        //dd($housingId);
        return Device::description($name)
            ->housing($housingId)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }

   


}
