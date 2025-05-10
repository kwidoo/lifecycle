<?php

namespace Kwidoo\Lifecycle\Contracts\Resolvers;

use Kwidoo\Lifecycle\Contracts\Lifecycle\Lifecycle;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;

/**
 * Interface for resolving lifecycle implementations
 */
interface LifecycleResolver
{
    /**
     * Resolve a lifecycle implementation based on provided options
     *
     * @param LifecycleOptionsData $options Options to configure the lifecycle
     * @return Lifecycle The resolved lifecycle implementation
     */
    public function resolve(LifecycleOptionsData $options): Lifecycle;
}
