<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Owner extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'owners';

    /**
     * The attribute that are mass assignable
     * @var array
     */
    protected $fillable = ['name', 'created_by', 'updated_by', 'app_last_version'];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function users()
    {
        return $this->hasMany('App\Models\User', 'owner_id');
    }

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }

    public function branches()
    {
        return $this->hasMany('App\Models\Branch');
    }

    public function serviceRules()
    {
        return $this->hasMany('App\Models\ServiceRule');
    }

    /**
     * For Searches by name
     */
    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('name', 'ILIKE', "%$name%");
        }
    }

    public static function filterAndPaginate($name)
    {

        return Owner::name($name)
            ->orderBy('id', 'desc')
            ->paginate();
    }


}