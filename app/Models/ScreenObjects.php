<?php

namespace App\Models;

use App\Models\ObjectType;
use App\Models\ObjectPropertyValue;
use Illuminate\Database\Eloquent\Model;

class ScreenObjects extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'screen_objects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'location_x', 'location_y', 'screen_id', 'object_type_id', 'version_hash'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    protected $appends = array('object_type_key', 'properties');

    public function getObjectTypeKeyAttribute()
    {
        $object_type = ObjectType::find($this->object_type_id);
        $key = $object_type->key;
        return $key;

    }

    public function getPropertiesAttribute()
    {
        return ObjectPropertyValue::where('screen_object_id', $this->id)->with('objectProperty')->get();
    }

    /**
     * Relationship between screenObject and ObjectPropertyValues
     */
    public function properties()
    {
        return $this->hasMany('App\Modules\Art\ObjectPropertyValue');
    }
    /**
     * Relationship between screen_objects and screens
     */
    public function screen()
    {
        return $this->belongsTo('App\Screen');
    }
    /**
     * Relationship between screen_objects and object_types
     */
    public function objectType()
    {
        return $this->belongsTo('App\Models\ObjectType');
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
     * Query for App with screen_id
     */
    public function scopeScreenId($query, $screen_id)
    {
        if (is_numeric($screen_id) && $screen_id > 0) {
            $query->where('screen_id', $screen_id);
        }
    }

    public static function filterAndPaginate($screen_id, $name)
    {

        return ScreenObjects::name($name)
            ->screenId($screen_id)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }
    
}
