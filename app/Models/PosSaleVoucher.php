<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosSaleVoucher extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'point_of_sale_vouchers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
    protected $appends = ['voucherCode'];
    public function voucherType()
    {
        return $this->belongsTo('App\Models\PointOfSaleVoucherType','pos_voucher_type_id');
    }
    public function pointOfSale()
    {
        return $this->belongsTo('App\Models\PointOfSale');
    }
    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }

    public static function filterAndPaginate($pos_id)
    {

        return PosSaleVoucher::where('point_of_sale_id', $pos_id)
            ->orderBy('id', 'desc')
            ->paginate();
    }
//    public function getValidFromAttribute($date)
//    {
//        $date = new Carbon($date);
//        return $date->format('d/m/Y');
//    }
//    public function getValidUntilAttribute($date)
//    {
//        $date = new Carbon($date);
//        return $date->format('d/m/Y');
//    }
//    public function getCreatedAtAttribute($date)
//    {
//        $date = new Carbon($date);
//        Carbon::setLocale('es');
//        return Carbon::now()->diffForHumans($date);
//        return $date->format('d/m/Y h:i');
//    }
//    public function getUpdatedAtAttribute($date)
//    {
//        $date = new Carbon($date);
//        return $date->format('d/m/Y h:i');
//    }

}
