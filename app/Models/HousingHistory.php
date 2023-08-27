<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HousingHistory extends Model
{
    //use SoftDeletes;

    protected $table = 'housing_history';

    protected $fillable = [ 'housing_id',
                            'last_atm_id',
                            'available',
                            'operation_type',
                            'created_by',
                            'updated_by',
                            'deleted_by',
                        ];

    protected $dates = [
                        'created_at', 
                        'updated_at',
                        'deleted_at'];

}
