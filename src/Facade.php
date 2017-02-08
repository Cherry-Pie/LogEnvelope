<?php

namespace Yaro\LogEnvelope;


class Facade extends \Illuminate\Support\Facades\Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'yaro.log-envelope';
    } // end getFacadeAccessor

}
