<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Multa extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'multas_alquiler';

    /**
     * The attribute that are mass assignable
     * @var array
     */

    protected $fillable = [ 
                            'alquiler_id',
                            'concepto',
                            'destination_operation_id',
                            'response',
                            'group_id',
                            'importe', 
                            'saldo', 
                            'num_venta',
                            'created_by'
                        ];

    protected $dates = [
                        'fecha_vencimiento',
                        'created_at', 
                        'updated_at',
                        'deleted_at'];

    // public function group()
    // {
    //     return $this->belongsTo('App\Models\Group', 'group_id');
    // }

 

}
