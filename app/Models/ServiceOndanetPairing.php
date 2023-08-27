<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOndanetPairing extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'services_ondanet_pairing';

    /**
     * The attribute that are mass assignable
     * @var array
     */
    protected $fillable = ['id', 'ondanet_code', 'service_request_id', 'service_source_id'];
}
