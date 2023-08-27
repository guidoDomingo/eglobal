<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParamsRule extends Model
{
    public $timestamps = false;
    
    protected $table = 'params_rule';

    protected $fillable = ['idparam_rules','description','type'];

    public function getKeyName()
    {
        return "idparam_rules";
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
        return ParamsRule::name($name)
            ->orderBy('description', 'desc')
            ->paginate(20);
    }



}
