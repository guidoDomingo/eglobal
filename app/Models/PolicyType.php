<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyType extends Model
{
    public $timestamps = false;
 
    protected $table = 'insurance_type';

    protected $fillable = ['id','description'];

    public function polizas()
    {
        return $this->hasMany('App\Models\InsurancePolicy', 'insurance_policy_type_id');
    }

}
