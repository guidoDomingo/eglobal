<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchPromotion extends Model
{
    public $timestamps = false;

    protected $table        = 'promotions_branches';
    protected $fillable     = ['promotions_providers_id', 'name', 'address', 'phone','provider_branch_id','latitud', 'longitud','business_id','start_time','end_time','location','custom_image'];
    

    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('name', 'ILIKE', "%$name%");
        }
    }


    // public function campaignsDetails()
    // {
    //     return $this->hasMany('App\Models\CampaignDetails', 'contents_id');
    // }

     
    public function business()
    {
        return $this->belongsTo('App\Models\BusinessPromotion');
    }

    public static function filterAndPaginate($name)
    {
        return BranchPromotion::name($name)
            ->orderBy('id', 'DESC')
            ->paginate();
    }
    
}
