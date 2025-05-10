<?php

namespace Kwidoo\Lifecycle\Resolvers;

use Illuminate\Contracts\Container\Container;
use Kwidoo\Lifecycle\Contracts\Resolvers\StrategyResolver;
use Kwidoo\Lifecycle\Contracts\Strategies\ErrorStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\EventStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\LogStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\RetryStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Lifecycle\LifecycleStrategies;
use Kwidoo\Lifecycle\Strategies\NoopEventStrategy;
use Kwidoo\Lifecycle\Strategies\NoopLogStrategy;
use Kwidoo\Lifecycle\Strategies\NoopRetryStrategy;
use Kwidoo\Lifecycle\Strategies\NoopTransactionStrategy;

/**
 * Resolves strategies based on configuration settings and lifecycle options
 */
class ConfigDrivenStrategyResolver implements StrategyResolver
{
    /**
     * @var array<string, array<bool, string>>
     */
    protected array $strategyMap;

    /**
     * @param Container $container Container for resolving strategy implementations
     */
    public function __construct(
        protected Container $container
    ) {
        $this->strategyMap = $this->buildStrategyMap();
    }

    /**
     * Resolve a lifecycle strategies object based on options
     *
     * @param LifecycleOptionsData $options Options that determine which strategies to enable
     * @return LifecycleStrategies Collection of resolved strategy implementations
     */
    public function resolve(LifecycleOptionsData $options): LifecycleStrategies
    {
        return new LifecycleStrategies(
            $this->resolveStrategy('event', $options->eventsEnabled),
            $this->resolveStrategy('transaction', $options->trxEnabled),
            $this->resolveStrategy('log', $options->loggingEnabled),
            $this->resolveStrategy('retry', $options->retryEnabled ?? false),
            $this->resolveStrategy('error', true),
        );
    }

    /**
     * Resolve a specific strategy based on type and whether it's enabled
     *
     * @param string $type Strategy type to resolve
     * @param bool $enabled Whether the strategy should be enabled
     * @return mixed The resolved strategy implementation
     */
    protected function resolveStrategy(string $type, bool $enabled): mixed
    {
        $className = $this->strategyMap[$type][$enabled] ?? $this->strategyMap[$type][true];
        return $this->container->make($className);
    }

    /**
     * Build the strategy map from configuration
     *
     * @return array<string, array<bool, string>>
     */
    protected function buildStrategyMap(): array
    {
        return [
            'event' => [
                true => config('lifecycle.strategies.event.enabled', EventStrategy::class),
                false => config('lifecycle.strategies.event.disabled', NoopEventStrategy::class),
            ],
            'transaction' => [
                true => config('lifecycle.strategies.transaction.enabled', TransactionStrategy::class),
                false => config('lifecycle.strategies.transaction.disabled', NoopTransactionStrategy::class),
            ],
            'log' => [
                true => config('lifecycle.strategies.log.enabled', LogStrategy::class),
                false => config('lifecycle.strategies.log.disabled', NoopLogStrategy::class),
            ],
            'retry' => [
                true => config('lifecycle.strategies.retry.enabled', RetryStrategy::class),
                false => config('lifecycle.strategies.retry.disabled', NoopRetryStrategy::class),
            ],
            'error' => [
                true => config('lifecycle.strategies.error.enabled', ErrorStrategy::class),
            ],
        ];
    }
}
