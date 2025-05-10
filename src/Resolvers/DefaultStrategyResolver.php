<?php

namespace Kwidoo\Lifecycle\Resolvers;

use Kwidoo\Lifecycle\Contracts\Resolvers\StrategyResolver;
use Kwidoo\Lifecycle\Contracts\Lifecycle\LifecycleStrategyResolver;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Lifecycle\LifecycleStrategies;

/**
 * Default implementation of StrategyResolver that uses the existing resolver
 *
 * This adapter pattern allows us to transition from the old LifecycleStrategyResolver
 * to the new StrategyResolver interface while maintaining backward compatibility.
 */
class DefaultStrategyResolver implements StrategyResolver
{
    /**
     * @param LifecycleStrategyResolver $legacyResolver The original strategy resolver
     */
    public function __construct(
        protected LifecycleStrategyResolver $legacyResolver
    ) {}

    /**
     * Resolve a lifecycle strategies object based on options
     *
     * @param LifecycleOptionsData $options Options that determine which strategies to enable
     * @return LifecycleStrategies Collection of resolved strategy implementations
     */
    public function resolve(LifecycleOptionsData $options): LifecycleStrategies
    {
        // Delegate to the legacy resolver
        return $this->legacyResolver->resolve($options);
    }
}
