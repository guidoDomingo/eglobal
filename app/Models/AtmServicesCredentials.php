<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtmServicesCredentials extends Model
{
    protected $table = 'atm_services_credentials';

    protected $fillable = ['atm_id','cnb_service_code','created_at','user','password','service_id','source_id'];
}
