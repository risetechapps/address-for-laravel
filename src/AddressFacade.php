<?php

namespace RiseTechApps\Address;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RiseTechApps\Address\Skeleton\SkeletonClass
 */
class AddressFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'address';
    }
}
