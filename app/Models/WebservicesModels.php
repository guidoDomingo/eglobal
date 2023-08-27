<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebservicesModels extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'services_model';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['service_id','key', 'value'];
}
