<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceLimited extends Model
{
    
    protected $table = 'reference_limited';

    protected $fillable = ['service_rule_id','current_params_rule_id','value','reference','frequency_last_updated','created_at','updated_at','black_list'];


    public function getKeyName()
    {
        return "service_rule_id";
    }

  
    public function paramsRule()
    {
        return $this->belongsTo('App\Models\ParamsRule');
    }

    public function serviceRule()
    {
        return $this->belongsTo('App\Models\ServiceRule');
    }

    public function scopeName($query, $name)
    {   
        if (trim($name) != "") {
            $query->where('reference', 'ILIKE', "%$name%");
        }

    }

    public static function filterAndPaginate($name)
    {
        return ReferenceLimited::name($name)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }



}
