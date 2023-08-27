<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pos extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'points_of_sale';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description', 
        'created_by', 
        'updated_by', 
        'pos_code', 
        'seller_type',
        'branch_id', 
        'ondanet_code', 
        'atm_id', 
        'owner_id',
        'deleted_at'
    ];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];


    public function pointOfSaleVoucherTypes(){
        return $this->hasMany('App\PosVoucherType');
    }
    public function pointOfSaleDeposits()
    {
        return $this->hasMany('App\Models\PosDeposits');
    }

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
    public function branch()
    {
        return $this->belongsTo('App\Models\Branch');
    }

    public function atm()
    {
        return $this->belongsTo('App\Models\Atm')->withTrashed();
    }

    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }

    /**
     * For Searches by name
     */
    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('description', 'ILIKE', "%$description%");
        }
    }

    public static function filterAndPaginate($description)
    {
        return Pos::description($description)
            ->whereNotNull('atm_id')
            ->orderBy('id', 'desc')
            ->paginate(20);
    }
}
