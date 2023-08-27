<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtmHasCampaign extends Model
{
    public $timestamps      = false;
    protected $table        = 'atm_has_campaigns';
    protected $fillable     = ['atm_id', 'campaigns_id', 'promotions_branches_id'];
  
    // public function scopeName($query, $name)
    // {
    //     if (trim($name) != "") {
    //         $query->where('title', 'ILIKE', "%$name%");
    //     }
    // }

   

    // public static function filterAndPaginate($name)
    // {

    //     return Art::name($name)
    //         //->with('categorias')
    //         //->with('service_sources')
    //         ->orderBy('id', 'DESC')
    //         ->paginate();
    // }
    
}
