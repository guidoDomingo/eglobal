<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebService extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'services';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['created_by', 'updated_by'];
    
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    /**
     * Relationship between WebService and WebServiceProvider
     */
    public function webServiceProvider()
    {
        return $this->belongsTo('App\Models\WebServiceProvider', 'service_provider_id');
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

    /**
     * Filter by description
     */
    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('name', 'ILIKE', "%$description%");
        }
    }
    /**
     * Filter by ServiceProvider
     */
    public function scopeServiceProviderId($query, $id)
    {
        if (is_numeric($id) && $id > 0) {
            $query->where('service_provider_id', $id);
        }
    }

    public static function filterAndPaginate($providerId, $name)
    {
        return WebService::description($name)
            ->serviceProviderId($providerId)
            ->orderBy('id', 'desc')
            ->with('webserviceprovider')
            ->paginate(20);
    }

    public function webservicerequests(){
        return $this->hasMany('App\Models\WebServiceRequest', 'service_id', 'id');
    }

}
