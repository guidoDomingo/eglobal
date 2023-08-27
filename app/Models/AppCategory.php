<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppCategory extends Model
{
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'app_categories';

    /**
     * The attribute that are mass assignable
     * @var array
     */
    // protected $fillable = ['name', 'created_by', 'updated_by', 'app_last_version'];

    // protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function marcas()
    {
        return $this->hasMany('App\Models\Marca', 'categoria_id');
    }
}