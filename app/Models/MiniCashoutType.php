<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class MiniCashoutType extends Model
{
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'mini_cashout_devolution_type';

    /**
     * The attribute that are mass assignable
     * @var array
     */
     protected $fillable = ['description'];

     protected $dates = ['created_at', 'updated_at'];

  
}