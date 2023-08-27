<?php namespace App\Models;

use Cartalyst\Sentinel\Activations\EloquentActivation;

class Activation extends EloquentActivation
{

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'eglobalt_auth';

}
