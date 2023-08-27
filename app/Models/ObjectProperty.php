<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectProperty extends Model
{
    protected $table = 'object_properties';
    protected $fillable = ['name', 'constraints', 'key', 'html_input'];

    /**
     * Relationship between properties and object
     */
    public function objectTypes()
    {
        return $this->belongsToMany('App\Models\ObjectType');
    }
}
