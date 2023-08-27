<?php
namespace App\Facades;
use Illuminate\Support\Facades\Facade;
use GuzzleHttp\Client;

class HttpClient extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'guzzle.client';
    }

}
