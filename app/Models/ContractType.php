<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractType extends Model
{
    public $timestamps = false;
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'contract_type';

    /**
     * The attribute that are mass assignable
     * @var array
     */

    protected $fillable = ['id','description'];




   
    
}
