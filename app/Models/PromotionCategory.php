<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionCategory extends Model
{
   // use SoftDeletes;
   
    protected $table        = 'promotions_categories';
    protected $fillable     = ['name', 'start_time', 'end_time'];
    public $timestamps = false;

    // public function scopeName($query, $name)
    // {
    //     if (trim($name) != "") {
    //         $query->where('name', 'ILIKE', "%$name%");
    //     }
    // }

    // public function getDateFormat()
    // {
    //     return 'Y-m-d H:i:s';
    // }
 
    // public function campaignsDetails()
    // {
    //     return $this->hasMany('App\Models\CampaignDetails', 'contents_id');
    // }

    // public static function filterAndPaginate($name)
    // {
    //     return Content::name($name)
    //         ->orderBy('id', 'DESC')
    //         ->paginate();
    // }
    
}
