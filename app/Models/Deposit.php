<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $table = 'deposits';

    public $fillable = array(
        'response',
        'ondanet_code',
        'boleta_deposito_id',
        'destination_operation_id'
    );
}
