<?php

namespace Kwidoo\Lifecycle;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kwidoo\Lifecycle\Skeleton\SkeletonClass
 */
class LifecycleFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'lifecycle';
    }
}
