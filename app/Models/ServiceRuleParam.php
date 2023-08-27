<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRuleParam extends Model
{
    public $timestamps = false;
    
    protected $table = 'service_rule_params';
    protected $fillable = ['service_rule_id','param_id','value','frequency'];




    // public function scopeName($query, $name)
    // {   
    //     if (trim($name) != "") {
    //         $query->where('serialnumber', 'ILIKE', "%$name%");
    //     }

    // }

    // public static function filterAndPaginate($name)
    // {
    //     return Housing::name($name)
    //         ->orderBy('id', 'desc')
    //         ->paginate(20);
    // }
}
