<?php
namespace Htunlogic\Poloniex;

use Illuminate\Support\Facades\Facade;

class Poloniex extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'poloniex';
    }
}