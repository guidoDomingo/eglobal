<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'products';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['description', 'cost', 'sell_price', 'owner_id', 'ondanet_code',
        'created_by', 'updated_by', 'currency', 'tax_type_id', 'product_provider_id'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function taxType(){
        return $this->belongsTo('App\Models\TaxType');
    }
    public function provider(){
        return $this->belongsTo('App\Models\Provider', 'product_provider_id', 'id');
    }
    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('description', 'ILIKE', "%$description%");
        }
    }

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }
    public static function filterAndPaginate($name)
    {
        return Product::description($name)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }
}
