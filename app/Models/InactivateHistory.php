<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InactivateHistory extends Model
{
    //use SoftDeletes;

    protected $table = 'atm_inactivate_history';

    protected $fillable = [ 
                            'atm_id',
                            'group_id',
                            'operation',
                            'data',
                            'created_by',
                            'updated_by',
                            'deleted_by',
                        ];

    protected $dates = [
                        'created_at', 
                        'updated_at',
                        'deleted_at'];

}
