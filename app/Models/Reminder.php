<?php namespace App\Models;

use Cartalyst\Sentinel\Reminders\EloquentReminder;

class Reminder extends EloquentReminder
{

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'eglobalt_auth';


}
