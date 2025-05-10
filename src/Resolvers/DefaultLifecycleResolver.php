<?php

namespace Kwidoo\Lifecycle\Resolvers;

use Illuminate\Contracts\Container\Container;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Lifecycle;
use Kwidoo\Lifecycle\Contracts\Resolvers\LifecycleResolver;
use Kwidoo\Lifecycle\Contracts\Resolvers\StrategyResolver;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;

/**
 * Default implementation of the LifecycleResolver interface
 */
class DefaultLifecycleResolver implements LifecycleResolver
{
    /**
     * @param Container $container Container for resolving Lifecycle implementations
     * @param StrategyResolver $strategyResolver For resolving strategy implementations
     */
    public function __construct(
        protected Container $container,
        protected StrategyResolver $strategyResolver
    ) {}

    /**
     * Resolve a lifecycle implementation based on provided options
     *
     * @param LifecycleOptionsData $options Options to configure the lifecycle
     * @return Lifecycle The resolved lifecycle implementation
     */
    public function resolve(LifecycleOptionsData $options): Lifecycle
    {
        // Determine which lifecycle implementation class to use from config
        $lifecycleClass = config(
            'lifecycle.implementations.lifecycle',
            \Kwidoo\Lifecycle\Lifecycle\DefaultLifecycle::class
        );

        // Resolve the strategies first
        $strategies = $this->strategyResolver->resolve($options);

        // Create a new lifecycle instance with the resolved strategies
        return $this->container->make($lifecycleClass, [
            'strategies' => $strategies,
            'options' => $options
        ]);
    }
}
