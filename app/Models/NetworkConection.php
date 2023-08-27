<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetworkConection extends Model
{

    public $timestamps = false;
    
    protected $table = 'network_conection';

    protected $fillable = ['id','internet_service_contract_id','description', 'network_technology_id', 'bandwidth', 'remote_access'];

    protected $dates = ['installation_date'];

    
}
