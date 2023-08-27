<?php namespace App\Models;

use Cartalyst\Sentinel\Throttling\EloquentThrottle;

class Throttle extends EloquentThrottle
{

    protected $connection = 'eglobalt_auth';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = array('banned_at', 'suspended_at');

    /**
     * User relationship for the throttle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

}
