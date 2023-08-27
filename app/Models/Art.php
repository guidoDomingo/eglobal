<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Art extends Model
{
    public $timestamps      = false;
    protected $table        = 'arts';
    protected $fillable     = ['image', 'title', 'duracionReprodu', 'duracionPausa','campaigns_id'];
  
    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('title', 'ILIKE', "%$name%");
        }
    }

    public function campaign()
    {
        return $this->hasOne('App\Models\Campaign');
    }

    public static function filterAndPaginate($name)
    {

        return Art::name($name)
            //->with('categorias')
            //->with('service_sources')
            ->orderBy('id', 'DESC')
            ->paginate();
    }
    
}
