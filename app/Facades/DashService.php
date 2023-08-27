<?php
namespace app\Facades;

use Illuminate\Support\Facades\Facade;

class DashService extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'dashservice';
    }
}