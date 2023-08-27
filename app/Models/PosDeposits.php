<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosDeposits extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'point_of_sale_deposit';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['deposit_code', 'ondanet_code', 'points_of_sale_id'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function pointOfSale()
    {
        return $this->belongsTo('App\Models\Pos');
    }

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }
}
