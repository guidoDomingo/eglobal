<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsurancePolicy extends Model
{
    public $timestamps = false;

    protected $table = 'insurance_policy';

    protected $fillable = ['id','insurance_code','insurance_policy_type_id','number', 'status', 'observaciones','created_by','capital'];

    protected $dates = ['created_at'];

    public function tipo()
    {
        return $this->belongsTo('App\Models\PolicyType','insurance_policy_type_id', 'id');
    }


}
