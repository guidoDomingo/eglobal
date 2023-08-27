<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessPromotion extends Model
{
    public $timestamps = false;

    protected $table        = 'business';
    protected $fillable     = ['description', 'created_at', 'status', 'minimum_radios','maximum_radios'];
    

    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('description', 'ILIKE', "%$name%");
        }
    }


 
    public function promotions_branches()
    {
        return $this->hasMany('App\Models\BranchPromotion', 'business_id');
    }

    public static function filterAndPaginate($name)
    {
        return BusinessPromotion::name($name)
            ->orderBy('id', 'DESC')
            ->paginate();
    }
    
}
