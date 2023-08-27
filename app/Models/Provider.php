<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'product_providers';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['business_name','mobile_phone','ci','address', 'created_by',
        'updated_by', 'ruc', 'owner_id'];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }

}
