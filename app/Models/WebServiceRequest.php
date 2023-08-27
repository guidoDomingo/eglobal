<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebServiceRequest extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'service_requests';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['created_by', 'updated_by'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
    /**
     * Filter by description
     */
    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('endpoint', 'ILIKE', "%$description%");
        }
    }

    /**
     * Relationship between WebServiceRequest and WebService
     */
    public function webservice()
    {
        return $this->belongsTo('App\WebServiceRequest');
    }
    public function serviceproviderproducts()
    {
        return $this->belongsToMany('App\Models\ServiceProviderProduct' ,'service_provider_product_service_request', 'service_request_id','service_provider_product_id');
    }

    /**
     * Returns related user
     * @return App\Modules\Auth\User
     */
    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
    /**
     * Returns related user
     * @return App\Modules\Auth\User
     */
    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }
    public static function filterAndPaginate($name)
    {
        return WebServiceRequest::description($name)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }

}
