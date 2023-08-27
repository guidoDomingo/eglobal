<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectType extends Model
{
    protected $table = 'object_types';
    protected $fillable = ['name', 'key'];

    public function objectProperties()
    {
        return $this->belongsToMany('App\Models\ObjectProperty');
    }
}
