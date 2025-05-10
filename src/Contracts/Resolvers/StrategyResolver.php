<?php

namespace Kwidoo\Lifecycle\Contracts\Resolvers;

use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Lifecycle\LifecycleStrategies;

/**
 * Interface for resolving strategy implementations based on lifecycle options
 */
interface StrategyResolver
{
    /**
     * Resolve a collection of strategies based on provided options
     *
     * @param LifecycleOptionsData $options Options that determine which strategies to enable
     * @return LifecycleStrategies Collection of resolved strategy implementations
     */
    public function resolve(LifecycleOptionsData $options): LifecycleStrategies;
}
