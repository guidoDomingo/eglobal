<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alquiler extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'alquiler';

    /**
     * The attribute that are mass assignable
     * @var array
     */

    protected $fillable = ['id','destination_operation_id', 'response', 'group_id', 'importe'];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function group()
    {
        return $this->belongsTo('App\Models\Group', 'group_id');
    }

    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('business_groups.description', 'ILIKE', "%$description%");
        }
    }

    public static function filterAndPaginate($description)
    {

        return Alquiler::description($description)
            ->select([
                'alquiler.*',
                'business_groups.description',
                'housing.serialnumber as num_serie'
            ])
            ->join('business_groups', 'business_groups.id', '=', 'alquiler.group_id')
            ->join('alquiler_housing', 'alquiler.id', '=', 'alquiler_housing.alquiler_id')
            ->join('housing', 'housing.id', '=', 'alquiler_housing.housing_id')
            ->with('group')
            ->orderBy('alquiler.id', 'desc')
            ->paginate(20);

    }

}
