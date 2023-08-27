<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Forms extends Model
{
    public $timestamps      = false;
    protected $table        = 'forms';
    protected $fillable     = ['label', 'data_type', 'valorminimo', 'valormaximo','campaigns_id'];
   
    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            // $query->where('label', 'ILIKE', "%$name%");
            $query->where('campaigns.name', 'ILIKE', "%$name%");
        }
    }
 
    public function campaign()
    {
        return $this->hasOne('App\Models\Campaign');
    }

    public static function filterAndPaginate($name)
    {

        return Forms::name($name)
            ->select([
                'forms.*',
                'campaigns.name'
            ])
            ->join('campaigns','campaigns.id','=','forms.campaigns_id')
            ->orderBy('campaigns.name', 'ASC')
            ->paginate(20);
    }
    
}
