<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebServiceProvider extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'service_providers';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'created_by', 'updated_by'];
    
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    /**
     * Relationship between WebService and WebServiceProvider
     */
    public function webServices()
    {
        return $this->hasMany('App\Models\WebService');
    }

    /**
     * Returns related user
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

    /**
     * Returns related user
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }

    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('name', 'ILIKE', "%$description%");
        }
    }


    public static function filterAndPaginate($name)
    {
        return WebServiceProvider::description($name)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }
    
}
