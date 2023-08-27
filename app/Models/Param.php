<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Param extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'params';
    /**
     * The attributes included from the model's JSON form.
     */
    protected $appends = array('value');

    protected $hidden = ['updated_by', 'created_by', 'created_at', 'updated_at', 'pivot'];
    /**
     * Relationship between applications and params
     */
    public function applications()
    {
        return $this->belongsToMany('App\Models\Applications')->withPivot('value');
    }

    public function getValueAttribute(){
        return $this->pivot->value;
    }
}
