<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionBranch extends Model
{
   
    protected $table        = 'promotions_branches';
    protected $fillable     = ['promotions_providers_id','name', 'address', 'phone','provider_branch_id','latitud','longitud','business_id'];
    public $timestamps = false;
    
}
