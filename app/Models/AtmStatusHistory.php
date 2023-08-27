<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class AtmStatusHistory extends Model
{
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'atm_status_history';

    /**
     * The attribute that are mass assignable
     * @var array
     */
     protected $fillable = ['atm_id', 'comments', 'status', 'diferencia'];

     protected $dates = ['created_at', 'updated_at'];

  
}