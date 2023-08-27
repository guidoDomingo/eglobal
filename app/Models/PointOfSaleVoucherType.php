<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PointOfSaleVoucherType extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'point_of_sale_voucher_types';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['expedition_point','voucher_type_id'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    /**
     * The attributes included from the model's JSON form.
     */
    protected $appends = ['description'];

    public function voucherType()
    {
        return $this->belongsTo('App\Models\VoucherType');
    }

    
    /**
     * Get description for voucher type
     */
    public function getDescription()
    {
        return $this->voucherType->description;
    }
    public function pointOfSale()
    {
        return $this->belongsTo('App\PointOfSale');
    }
    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('expedition_point', 'ILIKE', "%$description%");
        }
    }
    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }

    public static function filterAndPaginate($pos_id, $name)
    {

        return PointOfSaleVoucherType::where('point_of_sale_id', $pos_id)->with('VoucherType')
            ->description($name)
            ->orderBy('id', 'desc')
            ->paginate();
    }

}
