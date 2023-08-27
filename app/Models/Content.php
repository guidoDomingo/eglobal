<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use SoftDeletes;
   
    protected $table        = 'contents';
    protected $fillable     = ['name', 'description', 'image', 'price','precionormal','porcentajedescuento','provider_product_id'];
    protected $dates        = ['deleted_at', 'created_at', 'updated_at'];

    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('name', 'ILIKE', "%$name%");
        }
    }

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }
 
    public function campaignsDetails()
    {
        return $this->hasMany('App\Models\CampaignDetails', 'contents_id');
    }

    public static function filterAndPaginate($name)
    {
        return Content::name($name)
            ->orderBy('id', 'DESC')
            ->paginate();
    }
    
}
