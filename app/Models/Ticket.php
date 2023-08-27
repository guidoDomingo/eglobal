<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    public $timestamps      = false;
    protected $table        = 'tickets';
    protected $fillable     = ['campaigns_id', 'header', 'footer'];
   
    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('campaigns.name', 'ILIKE', "%$name%");
        }
    }
 
    public function campaign()
    {
        return $this->hasOne('App\Models\Campaign');
    }

    public static function filterAndPaginate($name)
    {

        return Ticket::name($name)
            ->select([
                'tickets.*',
                'campaigns.name'
            ])
            ->join('campaigns','campaigns.id','=','tickets.campaigns_id')
            ->orderBy('campaigns.name', 'ASC')
            ->paginate(20);
    }
    
}
