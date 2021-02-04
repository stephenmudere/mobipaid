<?php

namespace Stephenmudere\Mobipaid;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Stephenmudere\Mobipaid\Mobipaid
 */
class MobipaidFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Mobipaid';
    }
}
