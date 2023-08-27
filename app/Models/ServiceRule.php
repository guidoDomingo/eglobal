<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRule extends Model
{
    public $timestamps = false;
    
    protected $table = 'service_rule';

    protected $fillable = ['idservice_rule','description','owner_id','service_id','service_source_id','message_user','created_at', 'deleted_at','updated_at'];


    public function getKeyName()
    {
        return "idservice_rule";
    }

  
    public function owner()
    {
        return $this->belongsTo('App\Models\Owner');

    }

    public function servicioMarca()
    {
        return $this->belongsTo('App\Models\ServiciosMarca');

    }

    
    public function referencesLimiteds()
    {
        return $this->hasMany('App\Models\ReferenceLimited');
    }

    public function scopeName($query, $name)
    {   
        if (trim($name) != "") {
            $query->where('description', 'ILIKE', "%$name%");
        }

    }

    public static function filterAndPaginate($name)
    {
        return ServiceRule::name($name)
            ->orderBy('description', 'desc')
            ->paginate(20);
    }


}
