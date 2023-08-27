<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternetServiceContract extends Model
{
    use SoftDeletes;

    protected $table = 'internet_service_contract';

    protected $fillable = ['id','isp_id','contract_cod','isp_acount_number', 'status','created_by'];

    protected $dates = ['date_init', 'date_end', 'created_at', 'updated_at','deleted_at'];

    
}
