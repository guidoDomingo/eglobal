<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractInsurance extends Model
{

 
    protected $table = 'contract_insurance';

    protected $fillable = ['contract_id','insurance_policy_id'];


    
}
