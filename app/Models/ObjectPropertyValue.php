<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectPropertyValue extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'object_properties_values';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['object_property_id', 'screen_object_id', 'key', 'value']; //, 'object_property_id'

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['updated_at', 'created_at', 'id', 'screen_object_id'];

    /**
     * Relationship between ObjectPropertyValue and ScreenObject
     */
    public function screenObject()
    {
        return $this->belongsTo('App\Models\ScreenObjects');
    }
    /**
     * Relationship between ObjectPropertyValue and ObjectProperty
     */
    public function objectProperty()
    {
        return $this->belongsTo('App\Models\ObjectProperty');

    }
}
