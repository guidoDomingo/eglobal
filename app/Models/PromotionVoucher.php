<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionVoucher extends Model
{
    protected $table        = 'promotions_contents';
    protected $fillable     = ['campaigns_id', 'coupon_code', 'transaction_id', 'status','name','description','image'];

    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('coupon_code', 'ILIKE', "%$name%");
        }
    }

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    public function campaign()
    {
        return $this->hasOne('App\Models\Campaign');
    }

    public static function filterAndPaginate($name)
    {

        return PromotionVoucher::name($name)
            //->with('categorias')
            //->with('service_sources')
            ->orderBy('id', 'ASC')
            ->paginate();
    }
    
}
