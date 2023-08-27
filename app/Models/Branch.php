<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Branch extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'branches';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['description', 'phone', 'address', 'branch_code', 'owner_id', 'created_by', 'updated_by','user_id', 'latitud', 'longitud','group_id', 'executive_id', 'caracteristicas_id','barrio_id','more_info'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    /**
     * Relationship between branches and owners
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\Owner');
    }

    public function group()
    {
        return $this->belongsTo('App\Models\Group');
    }

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }

    public function scopeOwner($query, $ownerId)
    {
        //dd($query->toSql());

        if(!is_null($ownerId)){
            $query->where('owner_id', $ownerId);
        }
    }
    public function scopeGroup($query, $groupId)
    {
        if(!is_null($groupId)){
            $query->where('group_id', $groupId);
        }
    }
    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('description', 'ILIKE', "%$description%");
        }
    }

    public static function filterAndPaginate($ownerId, $name)
    {

        return Branch::description($name)
            ->owner($ownerId)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }

    public static function filterPaginate($groupId, $description)
    {

        return Branch::description($description)
            ->group($groupId)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }
}