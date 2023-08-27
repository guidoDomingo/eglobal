<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceProviderProduct extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'service_provider_products';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['created_by', 'updated_by','service_provider_id','description'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
    /**
     * Filter by description
     */
    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('description', 'ILIKE', "%$description%");
        }
    }

    /**
     * Merges the description of ServiceProviderProduct and its ServiceProvider name
     * @return type
     */
    public function getDescriptionProvidernameAttribute()
    {
        return $this->attributes['description'] .' - '. $this->webserviceprovider->name;
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

    /**
     * Relationship between WebService and WebServiceProvider
     */
    public function webserviceprovider()
    {
        return $this->belongsTo('App\Models\WebServiceProvider', 'service_provider_id');
    }

    /**
     * Relationship between ServiceProviderProduct and WebServiceRequest
     */
    public function webservicerequests()
    {
        return $this->belongsToMany('App\Models\WebServiceRequest', 'service_provider_product_service_request', 'service_provider_product_id', 'service_request_id');
    }

    /**
     * Relationship between ServiceProviderProduct and Products
     */
    public function products()
    {
        return $this->hasMany('App\Models\Products');
    }

    /**
     * Relationship between ServiceProviderProduct and Owner
     */
    public function owners()
    {
        return $this->belongsToMany('App\Models\Owner', 'service_provider_id', 'owner_service_provider_product', 'owner_id', 'service_provider_product_id')->withPivot('use_voucher_type', 'voucher_type_id', 'outcome_type_id');
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
    public static function filterAndPaginate($providerId, $name)
    {
        return ServiceProviderProduct::description($name)
            ->serviceProviderId($providerId)
            ->orderBy('id', 'desc')
            ->with('webserviceprovider')
            ->paginate(20);
    }
}
