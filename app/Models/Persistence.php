<?php namespace App\Models;

use Cartalyst\Sentinel\Persistences\EloquentPersistence;

class Persistence extends EloquentPersistence
{

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'eglobalt_auth';

}
