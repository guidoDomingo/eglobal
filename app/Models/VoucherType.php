<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoucherType extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'voucher_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['description', 'created_by', 'updated_by', 'voucher_type_code'];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
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
            $query->where('description', 'ILIKE', "%$description%");
        }
    }

    public static function filterAndPaginate($name)
    {
        return VoucherType::description($name)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }
}
