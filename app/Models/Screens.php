<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Screens extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'screens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'version_hash', 'application_id', 'refresh_time', 'service_provider_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Relationship between screens and applications
     */
    public function application()
    {
        return $this->belongsTo('App\Models\Applications');
    }

    public function objects()
    {
        return $this->hasMany('App\Models\ScreenObjects');
    }

    /**
     * For Searches by name
     */
    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('name', 'LIKE', "%$name%");
        }
    }

    /**
     * Query for App with app_id
     */
    public function scopeApplicationId($query, $app_id)
    {
        if (is_numeric($app_id) && $app_id > 0) {
            $query->where('application_id', $app_id);
        }
    }

    public static function filterAndPaginate($app_id, $name)
    {

        return Screens::name($name)
            ->applicationId($app_id)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }
    
}
